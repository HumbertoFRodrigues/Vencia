<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImplantacaoTest extends TestCase
{
    private const TOKEN = 'abcdefghijklmnopqrstuvwxyz0123456789';

    public function test_endpoint_does_not_exist_without_a_long_token(): void
    {
        config(['app.deploy_token' => null]);
        $this->postJson('/implantacao/concluir')->assertNotFound();

        config(['app.deploy_token' => 'curto']);
        $this->postJson('/implantacao/concluir', [], ['X-Deploy-Token' => 'curto'])->assertNotFound();
    }

    public function test_wrong_token_is_refused_and_nothing_runs(): void
    {
        config(['app.deploy_token' => self::TOKEN]);
        Artisan::shouldReceive('call')->never();

        $this->postJson('/implantacao/concluir', [], ['X-Deploy-Token' => strrev(self::TOKEN)])->assertForbidden();
        $this->postJson('/implantacao/concluir')->assertForbidden();
    }

    public function test_right_token_runs_migrations_and_refreshes_caches(): void
    {
        config(['app.deploy_token' => self::TOKEN]);
        Artisan::shouldReceive('call')->with('migrate', ['--force' => true])->once()->andReturn(0);
        Artisan::shouldReceive('call')->with('optimize:clear', [])->once()->andReturn(0);
        Artisan::shouldReceive('call')->with('config:cache', [])->once()->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('ok');

        $this->postJson('/implantacao/concluir', [], ['X-Deploy-Token' => self::TOKEN])
            ->assertOk()->assertJson(['ok' => true]);
    }

    public function test_a_failed_migration_stops_and_reports_500(): void
    {
        config(['app.deploy_token' => self::TOKEN]);
        Artisan::shouldReceive('call')->with('migrate', ['--force' => true])->once()->andReturn(1);
        Artisan::shouldReceive('call')->with('optimize:clear', [])->never();
        Artisan::shouldReceive('output')->andReturn('erro');

        $this->postJson('/implantacao/concluir', [], ['X-Deploy-Token' => self::TOKEN])
            ->assertStatus(500)->assertJson(['ok' => false]);
    }
}
