<?php

namespace Tests\Feature\Contas;

use App\Enums\Papel;
use App\Livewire\Conta\Perfil;
use App\Livewire\Dashboard;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PerfilEPrimeirosPassosTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_has_a_profile_page(): void
    {
        $this->actingAs(User::factory()->papel(Papel::Registos)->create());

        $this->get('/conta/perfil')->assertOk()->assertSee('O meu perfil')->assertSee('Verificação em dois passos');
    }

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $user = User::factory()->create(['password' => 'antiga123']);
        $this->actingAs($user);

        Livewire::test(Perfil::class)->set('passwordActual', 'errada')->set('passwordNova', 'nova12345')->set('passwordNova_confirmation', 'nova12345')
            ->call('mudarPassword')->assertHasErrors('passwordActual');
        $this->assertTrue(Hash::check('antiga123', $user->fresh()->password));

        Livewire::test(Perfil::class)->set('passwordActual', 'antiga123')->set('passwordNova', 'nova12345')->set('passwordNova_confirmation', 'nova12345')
            ->call('mudarPassword')->assertHasNoErrors();
        $this->assertTrue(Hash::check('nova12345', $user->fresh()->password));
    }

    public function test_a_new_email_must_be_confirmed_again(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => 'segredo123']);
        $this->actingAs($user);

        Livewire::test(Perfil::class)->set('email', 'novo@t.mz')->call('guardarDados')->assertHasErrors('passwordActual');

        Livewire::test(Perfil::class)->set('email', 'novo@t.mz')->set('passwordActual', 'segredo123')->call('guardarDados')
            ->assertRedirect(route('verification.notice'));

        $user->refresh();
        $this->assertSame('novo@t.mz', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_the_last_owner_cannot_leave_but_a_member_can(): void
    {
        $dono = User::factory()->create();
        $membro = User::factory()->papel(Papel::Gestor)->create();

        $this->actingAs($dono);
        Livewire::test(Perfil::class)->call('sair', $this->conta->id)->assertDispatched('toast', fn ($n, $p) => $p['tone'] === 'danger');
        $this->assertSame(Papel::Dono, $dono->fresh()->papelNa($this->conta));

        $this->actingAs($membro);
        Livewire::test(Perfil::class)->call('sair', $this->conta->id);
        $this->assertNull($membro->fresh()->papelNa($this->conta));
    }

    public function test_a_new_business_sees_first_steps_that_tick_themselves_off(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Dashboard::class)->assertSee('Primeiros passos')->assertSee('0 de 4 feitos');

        Cliente::create(['nome' => 'A', 'email' => 'a@t.mz', 'status' => 'activo', 'desde' => '2026-01-01']);
        Livewire::test(Dashboard::class)->assertSee('1 de 4 feitos');

        Livewire::test(Dashboard::class)->call('dispensarPrimeirosPassos')->assertDontSee('Primeiros passos');
        $this->assertTrue(Configuracao::obter('onboarding')['dispensado']);
    }

    public function test_a_registos_member_only_sees_the_steps_they_can_do(): void
    {
        $this->actingAs(User::factory()->papel(Papel::Registos)->create());

        Livewire::test(Dashboard::class)->assertSee('0 de 2 feitos')->assertDontSee('Completa os dados da empresa')->assertDontSee('Convida a tua equipa');
    }

    public function test_terms_and_privacy_are_public(): void
    {
        $this->get('/termos')->assertOk()->assertSee('Termos de utilização')->assertSee('HTech Group');
        $this->get('/privacidade')->assertOk()->assertSee('Política de privacidade');
        $this->get('/registar')->assertSee(route('termos'));
    }
}
