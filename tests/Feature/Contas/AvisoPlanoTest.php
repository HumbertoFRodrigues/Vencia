<?php

namespace Tests\Feature\Contas;

use App\Enums\Papel;
use App\Models\Plano;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** The bar at the top of the app in the last days of a trial or a paid plan. */
class AvisoPlanoTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_the_owner_sees_the_trial_ending_in_the_last_days_only(): void
    {
        Carbon::setTestNow('2026-10-17');
        $this->actingAs(User::factory()->create());
        $pro = Plano::query()->where('slug', 'pro')->value('id');

        $this->conta->update(['plano_id' => $pro, 'plano_ate' => '2026-10-30', 'em_teste' => true]);
        $this->get('/painel')->assertOk()->assertDontSee('período de teste do Pro acaba');

        $this->conta->update(['plano_ate' => '2026-10-20']);
        $this->get('/painel')->assertSee('período de teste do Pro acaba em 3 dias');

        $this->conta->update(['plano_ate' => '2026-10-18', 'em_teste' => false]);
        $this->get('/painel')->assertSee('O teu plano Pro termina amanhã');
    }

    public function test_other_members_and_unlimited_plans_see_nothing(): void
    {
        Carbon::setTestNow('2026-10-17');
        $this->conta->update(['plano_id' => Plano::query()->where('slug', 'pro')->value('id'), 'plano_ate' => '2026-10-18', 'em_teste' => true]);

        $this->actingAs(User::factory()->papel(Papel::Gestor)->create());
        $this->get('/painel')->assertDontSee('Ver planos');

        $this->conta->update(['plano_ate' => null]);
        $this->actingAs(User::factory()->create());
        $this->get('/painel')->assertDontSee('Ver planos');
    }
}
