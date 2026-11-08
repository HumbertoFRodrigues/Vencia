<?php

namespace Tests\Feature;

use App\Support\Tarefas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/** The fallback that runs the daily tasks after a visit when the server has no cron jobs. */
class TarefasSemCronTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_task_is_due_once_its_hour_has_passed_and_it_has_not_run_since(): void
    {
        Carbon::setTestNow('2026-11-09 06:45');
        $this->assertSame(['copia', 'planos'], Tarefas::emFalta(), 'reminders are due at 07:00, not yet');

        Tarefas::registar('copia');
        Tarefas::registar('planos');
        $this->assertSame([], Tarefas::emFalta());

        Carbon::setTestNow('2026-11-09 08:00');
        $this->assertSame(['verificar'], Tarefas::emFalta());

        // Ran yesterday only: due again today.
        Carbon::setTestNow('2026-11-10 07:30');
        $this->assertSame(['copia', 'planos', 'verificar'], Tarefas::emFalta());
    }

    public function test_due_tasks_run_and_the_check_is_throttled(): void
    {
        Carbon::setTestNow('2026-11-09 09:00');
        Tarefas::registar('copia');
        Tarefas::registar('planos');

        Artisan::shouldReceive('call')->once()->with('vencia:verificar-vencimentos');

        $this->assertSame(['verificar'], Tarefas::correrAtrasadas());
        $this->assertSame([], Tarefas::correrAtrasadas(), 'checked again only after 10 minutes');
    }

    public function test_nothing_runs_while_another_visit_is_running_them(): void
    {
        Carbon::setTestNow('2026-11-09 09:00');
        Cache::lock('tarefas:a-correr', 900)->get();

        Artisan::shouldReceive('call')->never();

        $this->assertSame([], Tarefas::correrAtrasadas());
    }
}
