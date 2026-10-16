<?php

namespace Tests\Feature\Contas;

use App\Enums\Papel;
use App\Livewire\Admin\Faturas;
use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Livewire\Financas\FinancasIndex;
use App\Livewire\Pagamentos\PagamentosIndex;
use App\Livewire\Servicos\ServicoDetail;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Conta;
use App\Models\Convite;
use App\Models\Fatura;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Pagamento;
use App\Models\Plano;
use App\Models\Retirada;
use App\Models\Servico;
use App\Models\User;
use App\Services\Equipa;
use App\Services\Faturacao;
use App\Services\Retiradas;
use App\Support\ContaActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

/** One test per issue found in the security review of the multi-account conversion. */
class RevisaoSegurancaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Configuracao::create(['chave' => 'empresa', 'valor' => ['nome' => 'Empresa', 'email' => 'empresa@t.mz', 'moeda' => 'MZN']]);
        MetodoPagamentoOpcao::create(['nome' => 'mpesa', 'arquivado' => false]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function pagamento(): Pagamento
    {
        $cliente = Cliente::create(['nome' => 'C', 'email' => 'c@t.mz', 'status' => 'activo', 'desde' => '2026-01-01']);
        $servico = Servico::create([
            'cliente_id' => $cliente->id, 'nome' => 'S', 'categoria' => 'outro', 'valor' => 100, 'periodo' => 'mes',
            'periodicidade' => 'mensal', 'inicio' => '2026-01-01', 'vencimento' => now()->toDateString(), 'status' => 'activo',
        ]);

        return Pagamento::create(['data' => now()->toDateString(), 'cliente_id' => $cliente->id, 'servico_id' => $servico->id, 'valor' => 100, 'metodo' => 'mpesa', 'periodo' => '01/2026']);
    }

    public function test_a_registos_member_cannot_delete_payments(): void
    {
        $pagamento = $this->pagamento();
        $this->actingAs(User::factory()->papel(Papel::Registos)->create());

        Livewire::test(PagamentosIndex::class)->call('apagar', $pagamento->id)->assertForbidden();
        Livewire::test(PagamentosIndex::class)->assertDontSeeHtml('label="Apagar"');

        $this->assertSame(1, Pagamento::count());
    }

    public function test_only_the_owner_who_made_a_withdrawal_can_undo_it(): void
    {
        $dono = User::factory()->create();
        $this->actingAs($dono);
        $retirada = app(Retiradas::class)->retirar(500, Carbon::today(), 'mpesa', null);

        $this->actingAs(User::factory()->papel(Papel::Gestor)->create());
        Livewire::test(FinancasIndex::class)->call('anularRetirada', $retirada->id)->assertForbidden();
        $this->assertSame(1, Retirada::count());

        $this->actingAs($dono);
        Livewire::test(FinancasIndex::class)->call('anularRetirada', $retirada->id);
        $this->assertSame(0, Retirada::count());
    }

    public function test_approving_twice_extends_the_plan_only_once(): void
    {
        Carbon::setTestNow('2026-10-15');
        $admin = User::factory()->create(['super_admin' => true]);
        $fatura = $this->conta->faturas()->create(['plano_id' => Plano::query()->where('slug', 'pro')->value('id'), 'ciclo' => 'mensal', 'valor' => 500, 'estado' => Fatura::EM_ANALISE]);
        $this->conta->update(['plano_id' => Plano::gratis()->id, 'plano_ate' => null]);

        app(Faturacao::class)->marcarPaga($fatura, $admin);
        app(Faturacao::class)->marcarPaga(Fatura::find($fatura->id), $admin);

        $this->assertSame('2026-11-14', $this->conta->fresh()->plano_ate->toDateString());
    }

    public function test_a_paid_invoice_cannot_be_rejected(): void
    {
        $admin = User::factory()->create(['super_admin' => true]);
        $fatura = $this->conta->faturas()->create(['plano_id' => Plano::query()->where('slug', 'pro')->value('id'), 'ciclo' => 'mensal', 'valor' => 500, 'estado' => Fatura::PAGA]);

        $this->expectException(RuntimeException::class);
        app(Faturacao::class)->rejeitar($fatura, $admin, 'engano');
    }

    public function test_the_admin_reject_dialog_on_a_stale_invoice_just_warns(): void
    {
        $admin = User::factory()->create(['super_admin' => true]);
        $fatura = $this->conta->faturas()->create(['plano_id' => Plano::query()->where('slug', 'pro')->value('id'), 'ciclo' => 'mensal', 'valor' => 500, 'estado' => Fatura::EM_ANALISE]);
        $this->actingAs($admin);

        $teste = Livewire::test(Faturas::class)->call('abrirRejeitar', $fatura->id);
        $fatura->update(['estado' => Fatura::PAGA]);
        $teste->set('motivo', 'x')->call('rejeitar')->assertDispatched('toast', fn ($n, $p) => $p['tone'] === 'danger');

        $this->assertSame(Fatura::PAGA, $fatura->fresh()->estado);
    }

    public function test_months_do_not_overflow_at_the_end_of_the_month(): void
    {
        Carbon::setTestNow('2026-01-31');
        $fatura = $this->conta->faturas()->create(['plano_id' => Plano::query()->where('slug', 'pro')->value('id'), 'ciclo' => 'mensal', 'valor' => 500, 'estado' => Fatura::EM_ANALISE]);
        $this->conta->update(['plano_id' => Plano::gratis()->id, 'plano_ate' => null]);

        app(Faturacao::class)->marcarPaga($fatura);

        $this->assertSame('2026-02-27', $this->conta->fresh()->plano_ate->toDateString(), 'not 2 March');
    }

    public function test_paying_during_a_trial_keeps_the_days_left(): void
    {
        Carbon::setTestNow('2026-10-15');
        $pro = Plano::query()->where('slug', 'pro')->sole();
        $this->conta->update(['plano_id' => $pro->id, 'plano_ate' => '2026-10-25', 'em_teste' => true]);
        $fatura = $this->conta->faturas()->create(['plano_id' => $pro->id, 'ciclo' => 'mensal', 'valor' => 500, 'estado' => Fatura::EM_ANALISE]);

        app(Faturacao::class)->marcarPaga($fatura);

        $conta = $this->conta->fresh();
        $this->assertSame('2026-11-25', $conta->plano_ate->toDateString());
        $this->assertFalse($conta->em_teste);
    }

    public function test_the_daily_plan_check_reaches_every_expired_conta(): void
    {
        Carbon::setTestNow('2026-10-15');
        $pro = Plano::query()->where('slug', 'pro')->value('id');
        $contas = collect(range(1, 5))->map(fn ($i) => Conta::create(['nome' => "C$i", 'plano_id' => $pro, 'plano_ate' => '2026-10-01']));

        app(Faturacao::class)->verificarPlanos();

        foreach ($contas as $c) {
            $this->assertSame('gratis', $c->fresh()->planoEfectivo()->slug);
        }
    }

    public function test_test_emails_only_go_to_the_business_own_addresses(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'eu@t.mz']));

        Livewire::test(ConfiguracoesIndex::class, ['tab' => 'empresa'])
            ->set('testeTipo', 'simples')->set('testeDestino', 'vitima@outro.mz')->call('enviarTeste')
            ->assertHasErrors('testeDestino');

        Livewire::test(ConfiguracoesIndex::class, ['tab' => 'empresa'])
            ->set('testeTipo', 'simples')->set('testeDestino', 'eu@t.mz')->call('enviarTeste')
            ->assertHasNoErrors();
    }

    public function test_an_invite_cannot_be_accepted_once_the_plan_has_no_room(): void
    {
        $conta = $this->outraConta();
        ContaActual::definir($conta);
        $dono = User::factory()->create();
        $convite = Convite::create(['conta_id' => $conta->id, 'email' => 'novo@t.mz', 'papel' => Papel::Gestor, 'token' => hash('sha256', 'x'), 'convidado_por' => $dono->id, 'expira_em' => now()->addDay()]);
        ContaActual::definir(null);
        $novo = User::factory()->create(['email' => 'novo@t.mz']);

        $this->expectException(RuntimeException::class);
        app(Equipa::class)->aceitar($convite, $novo);
    }

    public function test_super_admins_and_blocked_users_cannot_be_entered_as(): void
    {
        $admin = User::factory()->create(['super_admin' => true]);
        $outroAdmin = User::factory()->create(['super_admin' => true]);
        $bloqueado = User::factory()->create(['bloqueado_em' => now()]);

        $this->actingAs($admin)->post(route('admin.entrar-como', $outroAdmin))->assertRedirect();
        $this->assertAuthenticatedAs($admin);
        $this->actingAs($admin)->post(route('admin.entrar-como', $bloqueado))->assertRedirect();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_detail_page_stops_working_after_switching_conta(): void
    {
        $pagamento = $this->pagamento();
        $user = User::factory()->create();
        $outra = $this->outraConta();
        $user->contas()->attach($outra->id, ['papel' => Papel::Dono->value]);
        $this->actingAs($user);

        $pagina = Livewire::test(ServicoDetail::class, ['servico' => $pagamento->servico]);

        ContaActual::definir($outra);
        $pagina->call('$refresh')->assertNotFound();
    }

    public function test_a_person_owns_at_most_three_contas(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 2) as $i) {
            $user->contas()->attach($this->outraConta("Extra $i")->id, ['papel' => Papel::Dono->value]);
        }
        ContaActual::definir(null);

        $this->actingAs($user)->post(route('conta.guardar'), ['nome' => 'Quarta'])->assertSessionHasErrors('nome');
        $this->assertSame(3, $user->contas()->count());
    }
}
