<?php

namespace Tests\Feature\Contas;

use App\Enums\Papel;
use App\Livewire\Conta\Equipa;
use App\Mail\PlataformaMail;
use App\Models\BibliotecaServico;
use App\Models\Configuracao;
use App\Models\Convite;
use App\Models\Plano;
use App\Models\User;
use App\Services\Equipa as EquipaService;
use App\Support\ContaActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class RegistoEConvitesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        ContaActual::definir(null);
    }

    private function registar(array $dados = []): TestResponse
    {
        return $this->post('/registar', $dados + [
            'name' => 'Ana Silva', 'email' => 'ana@exemplo.mz', 'empresa' => 'Ana Digital',
            'password' => 'segredo123', 'password_confirmation' => 'segredo123',
        ]);
    }

    public function test_the_public_home_shows_the_plans(): void
    {
        $this->get('/')->assertOk()->assertSee('Criar conta')->assertSee('Grátis')->assertSee('Pro');
    }

    public function test_signing_up_creates_a_conta_with_a_pro_trial_and_asks_to_confirm_the_email(): void
    {
        $this->registar()->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'ana@exemplo.mz')->sole();
        $conta = $user->contaActual();

        $this->assertSame('Ana Digital', $conta->nome);
        $this->assertSame(Papel::Dono, $user->papelNa($conta));
        $this->assertTrue($conta->em_teste);
        $this->assertSame('pro', $conta->planoEfectivo()->slug);
        $this->assertSame(today()->addDays(14)->toDateString(), $conta->plano_ate->toDateString());
        Mail::assertSent(PlataformaMail::class, fn ($m) => $m->hasTo('ana@exemplo.mz') && str_contains($m->assunto, 'Confirma'));

        // The new conta comes with everything it needs.
        ContaActual::definir($conta);
        $this->assertSame('Ana Digital', Configuracao::obter('empresa')['nome']);
        $this->assertSame(13, BibliotecaServico::count());

        // Until the email is confirmed, the app is closed.
        $this->get('/painel')->assertRedirect(route('verification.notice'));

        $link = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($link)->assertRedirect(route('dashboard'));
        $this->get('/painel')->assertOk();
    }

    public function test_without_a_trial_new_contas_start_on_free(): void
    {
        Configuracao::guardarPlataforma('registo', ['aberto' => true, 'dias_teste' => 0]);

        $this->registar();

        $this->assertSame('gratis', User::query()->sole()->contaActual()->planoEfectivo()->slug);
    }

    public function test_sign_up_can_be_closed_and_bots_are_ignored(): void
    {
        $this->registar(['website' => 'http://spam'])->assertRedirect(route('inicio'));
        $this->assertSame(0, User::count());

        Configuracao::guardarPlataforma('registo', ['aberto' => false, 'dias_teste' => 0]);
        $this->registar()->assertSessionHasErrors('email');
        $this->assertSame(0, User::count());
    }

    public function test_the_same_email_cannot_sign_up_twice(): void
    {
        $this->registar();
        auth()->logout();

        $this->registar(['name' => 'Outra'])->assertSessionHasErrors('email');
        $this->assertSame(1, User::count());
    }

    public function test_an_invited_person_signs_up_straight_into_the_team(): void
    {
        ContaActual::definir($this->conta);
        $dono = User::factory()->create();
        app(EquipaService::class)->convidar($this->conta, 'novo@exemplo.mz', Papel::Registos, $dono);

        $url = null;
        Mail::assertSent(PlataformaMail::class, function ($m) use (&$url) {
            $url = $m->url;

            return $m->hasTo('novo@exemplo.mz');
        });
        $token = basename($url);
        ContaActual::definir(null);

        $this->get('/convite/'.$token)->assertOk()->assertSee('Conta de teste');

        $this->post('/registar', [
            'convite' => $token, 'name' => 'Novo', 'email' => 'novo@exemplo.mz',
            'password' => 'segredo123', 'password_confirmation' => 'segredo123',
        ])->assertRedirect(route('dashboard'));

        $novo = User::query()->where('email', 'novo@exemplo.mz')->sole();
        $this->assertTrue($novo->hasVerifiedEmail(), 'the invite proves the address');
        $this->assertSame(1, $novo->contas()->count(), 'no conta of their own');
        $this->assertSame(Papel::Registos, $novo->papelNa($this->conta));
        $this->assertNotNull(Convite::query()->sole()->aceite_em);

        $this->get('/financas')->assertForbidden();
        $this->get('/clientes')->assertOk();
    }

    public function test_an_existing_user_accepts_with_the_invited_email_only(): void
    {
        ContaActual::definir($this->conta);
        $dono = User::factory()->create();
        app(EquipaService::class)->convidar($this->conta, 'existe@exemplo.mz', Papel::Gestor, $dono);
        $token = null;
        Mail::assertSent(PlataformaMail::class, function ($m) use (&$token) {
            $token = basename($m->url);

            return true;
        });

        $outraConta = $this->outraConta('Empresa própria');
        ContaActual::definir($outraConta);
        $existe = User::factory()->create(['email' => 'existe@exemplo.mz']);
        $estranho = User::factory()->create(['email' => 'estranho@exemplo.mz']);
        ContaActual::definir(null);

        $this->actingAs($estranho)->post('/convite/'.$token)->assertSessionHasErrors('email');
        $this->assertNull($estranho->papelNa($this->conta));

        $this->actingAs($existe)->post('/convite/'.$token)->assertRedirect(route('dashboard'));
        $existe->esquecerPapeis();
        $this->assertSame(Papel::Gestor, $existe->papelNa($this->conta));
        $this->assertSame(2, $existe->contas()->count());
        $this->assertSame($this->conta->id, $existe->fresh()->conta_actual_id, 'they land in the conta they just joined');
    }

    public function test_the_free_plan_limits_the_team_and_the_last_owner_stays(): void
    {
        $conta = $this->outraConta();
        ContaActual::definir($conta);
        $dono = User::factory()->create();
        $this->actingAs($dono);

        Livewire::test(Equipa::class)->set('email', 'a@exemplo.mz')->set('papel', 'gestor')->call('convidar')
            ->assertHasErrors('email');
        $this->assertSame(0, Convite::count(), 'Grátis has 1 member');

        $conta->update(['plano_id' => Plano::query()->where('slug', 'pro')->value('id')]);
        $conta->refresh();
        Livewire::test(Equipa::class)->set('email', 'a@exemplo.mz')->set('papel', 'gestor')->call('convidar')->assertHasNoErrors();
        $this->assertSame(1, Convite::count());

        Livewire::test(Equipa::class)->call('remover', $dono->id)->assertDispatched('toast', fn ($n, $p) => $p['tone'] === 'danger');
        $this->assertSame(Papel::Dono, $dono->fresh()->papelNa($conta));
    }

    public function test_only_the_owner_sees_team_and_plan(): void
    {
        ContaActual::definir($this->conta);
        $gestor = User::factory()->papel(Papel::Gestor)->create();
        ContaActual::definir(null);

        $this->actingAs($gestor);
        $this->get('/conta/equipa')->assertForbidden();
        $this->get('/conta/plano')->assertForbidden();
        $this->get('/configuracoes')->assertOk();
    }
}
