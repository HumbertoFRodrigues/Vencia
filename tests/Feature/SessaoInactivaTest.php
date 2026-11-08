<?php

namespace Tests\Feature;

use App\Http\Middleware\TerminarSessaoInactiva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The session ends after 15 minutes without activity (config session.inactividade). */
class SessaoInactivaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_an_active_session_carries_on(): void
    {
        $this->withSession([TerminarSessaoInactiva::CHAVE => time() - 14 * 60])
            ->get('/painel')->assertOk();

        $this->assertAuthenticated();
    }

    public function test_after_15_quiet_minutes_the_next_page_goes_to_the_login_with_the_reason(): void
    {
        $this->withSession([TerminarSessaoInactiva::CHAVE => time() - 16 * 60])
            ->get('/painel')
            ->assertRedirect(route('login'))
            ->assertSessionHas('aviso', fn ($m) => str_contains($m, '15 minutos sem actividade'));

        $this->assertGuest();
        $this->get(route('login'))->assertSee('15 minutos sem actividade');
    }

    public function test_a_background_call_gets_401_so_the_page_can_send_the_person_to_the_login(): void
    {
        $this->withSession([TerminarSessaoInactiva::CHAVE => time() - 16 * 60])
            ->postJson(route('sessao.manter'))
            ->assertStatus(401)
            ->assertJson(['sessao' => 'terminada']);

        $this->assertGuest();
    }

    public function test_the_keep_alive_ping_refreshes_the_clock(): void
    {
        $this->withSession([TerminarSessaoInactiva::CHAVE => time() - 10 * 60])
            ->postJson(route('sessao.manter'))->assertNoContent();

        $this->assertGreaterThanOrEqual(time() - 2, session(TerminarSessaoInactiva::CHAVE));
    }

    public function test_the_countdown_logout_explains_why(): void
    {
        $this->post(route('logout'), ['motivo' => 'inactividade'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('aviso');
    }

    public function test_zero_turns_it_off(): void
    {
        config(['session.inactividade' => 0]);

        $this->withSession([TerminarSessaoInactiva::CHAVE => time() - 600 * 60])
            ->get('/painel')->assertOk();
    }

    public function test_the_page_carries_the_countdown_script(): void
    {
        $this->get('/painel')->assertSee('vencia:ultima-actividade', false)->assertSee('sessao-sair', false);
    }
}
