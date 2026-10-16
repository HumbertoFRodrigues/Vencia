<?php

namespace Tests\Feature;

use App\Livewire\Configuracoes\Seguranca;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoisPassosTest extends TestCase
{
    use RefreshDatabase;

    private function comDoisPassos(): User
    {
        $u = User::factory()->create(['email' => 'a@t.mz', 'password' => 'segredo123']);
        $u->forceFill([
            'dois_passos_segredo' => Totp::gerarSegredo(),
            'dois_passos_confirmado_em' => now(),
            'dois_passos_recuperacao' => ['aaaaa-bbbbb', 'ccccc-ddddd'],
        ])->save();

        return $u->fresh();
    }

    public function test_without_two_step_the_password_logs_in(): void
    {
        User::factory()->create(['email' => 'a@t.mz', 'password' => 'segredo123']);

        $this->post('/login', ['email' => 'a@t.mz', 'password' => 'segredo123'])->assertRedirect('/painel');
        $this->assertAuthenticated();
    }

    public function test_with_two_step_the_password_alone_is_not_enough(): void
    {
        $this->comDoisPassos();

        $this->post('/login', ['email' => 'a@t.mz', 'password' => 'segredo123'])->assertRedirect('/login/dois-passos');
        $this->assertGuest();
        $this->get('/painel')->assertRedirect('/login');
    }

    public function test_a_valid_code_logs_in_and_cannot_be_reused(): void
    {
        $u = $this->comDoisPassos();
        $codigo = Totp::codigo($u->dois_passos_segredo, Totp::passoActual());

        $this->post('/login', ['email' => 'a@t.mz', 'password' => 'segredo123']);
        $this->post('/login/dois-passos', ['codigo' => $codigo])->assertRedirect('/painel');
        $this->assertAuthenticatedAs($u);

        auth()->logout();
        $this->post('/login', ['email' => 'a@t.mz', 'password' => 'segredo123']);
        $this->post('/login/dois-passos', ['codigo' => $codigo])->assertSessionHasErrors('codigo');
        $this->assertGuest();
    }

    public function test_a_recovery_code_works_once(): void
    {
        $u = $this->comDoisPassos();

        $this->post('/login', ['email' => 'a@t.mz', 'password' => 'segredo123']);
        $this->post('/login/dois-passos', ['codigo' => 'AAAAA-BBBBB'])->assertRedirect('/painel');
        $this->assertSame(['ccccc-ddddd'], $u->fresh()->dois_passos_recuperacao);
    }

    public function test_wrong_codes_are_refused_and_the_step_expires(): void
    {
        $this->comDoisPassos();

        $this->post('/login', ['email' => 'a@t.mz', 'password' => 'segredo123']);
        $this->post('/login/dois-passos', ['codigo' => '000000'])->assertSessionHasErrors('codigo');
        $this->assertGuest();

        $this->travel(6)->minutes();
        $this->post('/login/dois-passos', ['codigo' => '123456'])->assertRedirect('/login');
    }

    public function test_activating_and_switching_off_from_configuracoes(): void
    {
        $u = User::factory()->create(['password' => 'segredo123']);
        $this->actingAs($u);

        $c = Livewire::test(Seguranca::class)->call('iniciar')->assertSet('codigo', '');
        $segredo = $u->fresh()->dois_passos_segredo;
        $this->assertNotNull($segredo);
        $this->assertFalse($u->fresh()->temDoisPassos(), 'not on until a code confirms the app is set up');

        $c->set('codigo', '000000')->call('confirmar')->assertHasErrors('codigo');
        $c->set('codigo', Totp::codigo($segredo, Totp::passoActual()))->call('confirmar')->assertHasNoErrors();
        $this->assertTrue($u->fresh()->temDoisPassos());
        $this->assertCount(8, $u->fresh()->dois_passos_recuperacao);

        $c->set('password', 'errada')->call('desactivar')->assertHasErrors('password');
        $c->set('password', 'segredo123')->call('desactivar');
        $this->assertFalse($u->fresh()->temDoisPassos());
    }

    public function test_the_secret_is_stored_encrypted(): void
    {
        $u = $this->comDoisPassos();
        $bruto = \DB::table('users')->where('id', $u->id)->value('dois_passos_segredo');

        $this->assertNotSame($u->dois_passos_segredo, $bruto);
    }
}
