<?php

namespace Tests\Feature\Contas;

use App\Livewire\Admin\Faturas;
use App\Livewire\Clientes\ClienteForm;
use App\Livewire\Conta\Plano as PlanoPage;
use App\Livewire\Pagamentos\PagamentosIndex;
use App\Mail\PlataformaMail;
use App\Models\Cliente;
use App\Models\Conta;
use App\Models\Fatura;
use App\Models\Plano;
use App\Models\User;
use App\Support\ContaActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PlanosEFaturacaoTest extends TestCase
{
    use RefreshDatabase;

    private Conta $gratis;

    private User $dono;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
        Carbon::setTestNow('2026-10-15 10:00');

        $this->admin = User::factory()->create(['super_admin' => true]);

        $this->gratis = $this->outraConta('Loja Grátis');
        ContaActual::definir($this->gratis);
        $this->dono = User::factory()->create();
        $this->actingAs($this->dono);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function criarClientes(int $n): void
    {
        for ($i = 1; $i <= $n; $i++) {
            Cliente::create(['nome' => "C$i", 'email' => "c$i@t.mz", 'status' => 'activo', 'desde' => '2026-01-01']);
        }
    }

    public function test_the_free_plan_stops_new_clients_at_its_limit_without_deleting_anything(): void
    {
        $this->criarClientes(10);

        Livewire::test(ClienteForm::class)->call('abrir')
            ->set('nome', 'Onze')->set('email', 'onze@t.mz')->set('status', 'activo')
            ->call('guardar')
            ->assertDispatched('toast', fn ($n, $p) => str_contains($p['title'], 'Limite'));

        $this->assertSame(10, Cliente::count());
    }

    public function test_exports_are_a_pro_feature(): void
    {
        Livewire::test(PagamentosIndex::class)->call('exportarCsv')
            ->assertDispatched('toast', fn ($n, $p) => str_contains($p['title'], 'Limite'))
            ->assertNoFileDownloaded();
    }

    public function test_paying_for_pro_end_to_end(): void
    {
        $pro = Plano::query()->where('slug', 'pro')->sole();

        // 1. The owner picks Pro, monthly.
        Livewire::test(PlanoPage::class)->call('escolher', $pro->id)->assertHasNoErrors();
        $fatura = Fatura::query()->sole();
        $this->assertSame(Fatura::PENDENTE, $fatura->estado);
        $this->assertEquals(500, $fatura->valor);

        // 2. Sends the M-Pesa reference and a proof.
        Livewire::test(PlanoPage::class)
            ->set('metodo', 'mpesa')->set('referencia', 'ABC123XYZ')
            ->set('comprovativo', UploadedFile::fake()->image('talao.jpg'))
            ->call('enviar')->assertHasNoErrors();
        $fatura->refresh();
        $this->assertSame(Fatura::EM_ANALISE, $fatura->estado);
        Storage::disk('local')->assertExists($fatura->comprovativo_path);
        Mail::assertSent(PlataformaMail::class, fn ($m) => $m->hasTo($this->admin->email));

        // The proof is visible to the admin and the owner only.
        $this->get(route('faturas.comprovativo', $fatura))->assertOk();
        ContaActual::definir($this->conta);
        $estranho = User::factory()->create();
        $this->actingAs($estranho)->get(route('faturas.comprovativo', $fatura))->assertNotFound();

        // 3. The super-admin confirms it.
        $this->actingAs($this->admin);
        Livewire::test(Faturas::class)->call('aprovar', $fatura->id);

        $this->gratis->refresh();
        $this->assertSame('pro', $this->gratis->planoEfectivo()->slug);
        $this->assertSame('2026-11-14', $this->gratis->plano_ate->toDateString());
        $this->assertSame(Fatura::PAGA, $fatura->fresh()->estado);
        Mail::assertSent(PlataformaMail::class, fn ($m) => $m->hasTo($this->dono->email) && $m->assunto === 'Pagamento confirmado');

        // The receipt downloads as a PDF for the owner.
        $this->actingAs($this->dono)->get(route('faturas.recibo', $fatura))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        // 4. Renewing early adds a month after the current end.
        ContaActual::definir($this->gratis);
        Livewire::test(PlanoPage::class)->call('escolher', $pro->id);
        $segunda = Fatura::query()->where('estado', Fatura::PENDENTE)->sole();
        $this->actingAs($this->admin);
        Livewire::test(Faturas::class)->call('aprovar', $segunda->id);
        $this->assertSame('2026-12-14', $this->gratis->fresh()->plano_ate->toDateString());

        // 5. Without renewing: warned a week before, back to Grátis after the end. Data stays.
        $this->criarClientes(12);
        Carbon::setTestNow('2026-12-08');
        $this->artisan('vencia:planos')->assertSuccessful();
        Mail::assertSent(PlataformaMail::class, fn ($m) => str_contains($m->assunto, 'está a acabar'));

        Carbon::setTestNow('2026-12-15');
        $this->artisan('vencia:planos')->assertSuccessful();
        $this->gratis->refresh();
        $this->assertSame('gratis', $this->gratis->planoEfectivo()->slug);
        $this->assertNull($this->gratis->plano_ate);
        ContaActual::definir($this->gratis);
        $this->assertSame(12, Cliente::count(), 'nothing is deleted');
    }

    public function test_a_rejected_payment_tells_the_owner_why(): void
    {
        $fatura = $this->gratis->faturas()->create([
            'plano_id' => Plano::query()->where('slug', 'pro')->value('id'), 'ciclo' => 'anual', 'valor' => 5000,
            'estado' => Fatura::EM_ANALISE, 'metodo' => 'mpesa', 'referencia' => 'X',
        ]);

        $this->actingAs($this->admin);
        Livewire::test(Faturas::class)->call('abrirRejeitar', $fatura->id)->set('motivo', 'Referência não encontrada')->call('rejeitar');

        $this->assertSame(Fatura::REJEITADA, $fatura->fresh()->estado);
        $this->assertSame('gratis', $this->gratis->fresh()->planoEfectivo()->slug);
        Mail::assertSent(PlataformaMail::class, fn ($m) => $m->hasTo($this->dono->email) && in_array('Motivo: Referência não encontrada', $m->paragrafos, true));
    }

    public function test_the_admin_area_is_for_super_admins_only(): void
    {
        foreach (['/admin', '/admin/contas', '/admin/utilizadores', '/admin/faturas', '/admin/planos', '/admin/plataforma', '/admin/actividade'] as $url) {
            $this->actingAs($this->dono)->get($url)->assertForbidden();
            $this->actingAs($this->admin)->get($url)->assertOk();
        }

        $this->actingAs($this->admin)->get('/admin/contas/'.$this->gratis->id)->assertOk()->assertSee('Loja Grátis');
    }

    public function test_entering_as_a_customer_and_coming_back(): void
    {
        $this->actingAs($this->admin)->post(route('admin.entrar-como', $this->dono))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->dono);
        $this->get('/painel')->assertOk()->assertSee('Voltar ao admin');

        $this->post(route('suporte.voltar'))->assertRedirect(route('admin.utilizadores'));
        $this->assertAuthenticatedAs($this->admin);
        $this->assertDatabaseHas('actividade_admin', ['accao' => 'entrar_como', 'user_id' => $this->admin->id]);

        // A customer can't use it.
        $this->actingAs($this->dono)->post(route('admin.entrar-como', $this->admin))->assertForbidden();
    }

    public function test_the_daily_reminders_run_for_every_conta_with_its_own_settings(): void
    {
        $this->artisan('vencia:verificar-vencimentos')->expectsOutputToContain('Contas: 2')->assertSuccessful();
    }
}
