<?php

namespace Tests\Feature;

use App\Models\Configuracao;
use App\Models\User;
use App\Services\CopiaSeguranca;
use App\Support\AlertaErro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class CopiaSegurancaEAlertasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(CopiaSeguranca::class, new class extends CopiaSeguranca
        {
            public function pasta(): string
            {
                return storage_path('framework/testing/copias');
            }
        });
        File::deleteDirectory(storage_path('framework/testing/copias'));
    }

    /** Messages captured by the in-memory "array" mailer the test suite uses. */
    private function enviados(): array
    {
        return app('mailer')->getSymfonyTransport()->messages()->all();
    }

    public function test_backup_contains_structure_and_data_and_keeps_the_last_14(): void
    {
        User::factory()->create(['email' => 'dono@teste.mz']);
        $copias = app(CopiaSeguranca::class);

        $sql = gzdecode(file_get_contents($copias->criar()));
        $this->assertStringContainsString('CREATE TABLE', $sql);
        $this->assertStringContainsString("'dono@teste.mz'", $sql);
        $this->assertStringContainsString('SET FOREIGN_KEY_CHECKS=0', $sql);

        foreach (range(1, 20) as $i) {
            touch($copias->pasta().sprintf('/vencia-2026-01-%02d_000000.sql.gz', $i));
        }
        $copias->limpar();
        $this->assertCount(CopiaSeguranca::MANTER, $copias->existentes());
    }

    public function test_backup_is_emailed_when_smtp_is_configured(): void
    {
        Configuracao::create(['chave' => 'empresa', 'valor' => ['email' => 'empresa@teste.mz']]);

        $this->artisan('vencia:copia-seguranca')->assertSuccessful();

        $this->assertCount(1, app(CopiaSeguranca::class)->existentes());
        $enviados = $this->enviados();
        $this->assertCount(1, $enviados);
        $this->assertCount(1, $enviados[0]->getOriginalMessage()->getAttachments());
    }

    public function test_errors_are_emailed_once_an_hour_in_production(): void
    {
        config(['app.alertas_erro' => true]);
        Configuracao::create(['chave' => 'empresa', 'valor' => ['email' => 'empresa@teste.mz']]);

        $erro = new RuntimeException('Base de dados indisponível');
        AlertaErro::notificar($erro);
        AlertaErro::notificar($erro);

        $this->assertCount(1, $this->enviados());
        $this->assertStringContainsString('Base de dados indisponível', $this->enviados()[0]->getOriginalMessage()->getTextBody());
    }

    public function test_no_error_emails_outside_production(): void
    {
        config(['app.alertas_erro' => false]);

        AlertaErro::notificar(new RuntimeException('x'));

        $this->assertCount(0, $this->enviados());
    }
}
