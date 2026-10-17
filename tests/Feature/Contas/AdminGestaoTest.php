<?php

namespace Tests\Feature\Contas;

use App\Enums\Papel;
use App\Livewire\Admin\ContaDetalhe;
use App\Livewire\Admin\Utilizadores;
use App\Livewire\Admin\Visao;
use App\Mail\PlataformaMail;
use App\Models\Configuracao;
use App\Models\User;
use App\Support\ContaActual;
use App\Support\Tarefas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** The super-admin managing users and contas, and keeping an eye on the system. */
class AdminGestaoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->create(['super_admin' => true]);
        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_the_admin_edits_a_users_name_and_email(): void
    {
        $user = User::factory()->unverified()->create(['name' => 'Ana Erro', 'email' => 'ana@errado.mz']);
        User::factory()->create(['email' => 'ocupado@t.mz']);

        Livewire::test(Utilizadores::class)->call('editar', $user->id)->set('editEmail', 'ocupado@t.mz')->call('guardarEdicao')->assertHasErrors('editEmail');

        Livewire::test(Utilizadores::class)->call('editar', $user->id)->set('editNome', 'Ana Certa')->set('editEmail', 'Ana@Certo.mz')->call('guardarEdicao')->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Ana Certa', $user->name);
        $this->assertSame('ana@certo.mz', $user->email);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertDatabaseHas('actividade_admin', ['accao' => 'user.editado']);
    }

    public function test_the_admin_renames_a_conta_and_gives_pro_days(): void
    {
        Carbon::setTestNow('2026-10-17');
        $conta = $this->outraConta('Nome Antigo');

        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->set('nome', 'Nome Novo')->call('guardarNome');
        $this->assertSame('Nome Novo', $conta->fresh()->nome);

        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->call('oferecerDias', 14);
        $conta->refresh();
        $this->assertSame('pro', $conta->planoEfectivo()->slug);
        $this->assertSame('2026-10-31', $conta->plano_ate->toDateString());

        // On top of the days still left.
        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->call('oferecerDias', 7);
        $this->assertSame('2026-11-07', $conta->fresh()->plano_ate->toDateString());
    }

    public function test_the_admin_manages_the_members_of_a_conta(): void
    {
        $conta = $this->outraConta();
        ContaActual::definir($conta);
        $dono = User::factory()->create();
        ContaActual::definir(null);
        $outro = User::factory()->create(['email' => 'outro@t.mz']);
        ContaActual::definir($this->conta);

        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->set('novoMembroEmail', 'ninguem@t.mz')->call('adicionarMembro')->assertHasErrors('novoMembroEmail');
        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->set('novoMembroEmail', 'outro@t.mz')->set('novoMembroPapel', 'registos')->call('adicionarMembro')->assertHasNoErrors();
        $this->assertSame(Papel::Registos, $outro->fresh()->papelNa($conta));

        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->call('mudarPapelMembro', $outro->id, 'gestor');
        $this->assertSame(Papel::Gestor, $outro->fresh()->papelNa($conta));

        // The last owner stays.
        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->call('removerMembro', $dono->id)->assertDispatched('toast', fn ($n, $p) => $p['tone'] === 'danger');
        $this->assertSame(Papel::Dono, $dono->fresh()->papelNa($conta));

        Livewire::test(ContaDetalhe::class, ['conta' => $conta])->call('removerMembro', $outro->id);
        $this->assertNull($outro->fresh()->papelNa($conta));
    }

    public function test_the_overview_shows_what_is_missing_on_the_server(): void
    {
        Livewire::test(Visao::class)->assertSee('Estado do sistema')->assertSee('Sem SMTP')->assertSee('Nunca correu');

        Configuracao::guardarPlataforma('smtp', ['servidor' => 'mail.exemplo.mz']);
        Tarefas::registar('verificar', '3 contas');
        Livewire::test(Visao::class)->assertSee('SMTP da plataforma: mail.exemplo.mz')->assertSee('3 contas');
    }

    public function test_the_daily_commands_record_when_they_ran(): void
    {
        $this->artisan('vencia:planos')->assertSuccessful();

        $this->assertFalse(Tarefas::estado()['planos']['atrasada']);
        $this->assertTrue(Tarefas::estado()['verificar']['atrasada']);
    }

    public function test_admins_hear_about_new_sign_ups_unless_they_turn_it_off(): void
    {
        auth()->logout();
        ContaActual::definir(null);

        $this->post('/registar', ['name' => 'Nova', 'email' => 'nova@t.mz', 'empresa' => 'Loja Nova', 'password' => 'segredo123', 'password_confirmation' => 'segredo123']);
        Mail::assertSent(PlataformaMail::class, fn ($m) => $m->hasTo($this->admin->email) && $m->assunto === 'Nova conta: Loja Nova');

        auth()->logout();
        Configuracao::guardarPlataforma('registo', ['aberto' => true, 'dias_teste' => 0, 'avisar_admin' => false]);
        $this->post('/registar', ['name' => 'Outra', 'email' => 'outra@t.mz', 'empresa' => 'Loja Outra', 'password' => 'segredo123', 'password_confirmation' => 'segredo123']);
        Mail::assertNotSent(PlataformaMail::class, fn ($m) => $m->assunto === 'Nova conta: Loja Outra');
    }
}
