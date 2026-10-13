<?php

namespace Tests\Feature\Negocio;

use App\Enums\ServicoStatus;
use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\LembreteEnviado;
use App\Models\Servico;
use App\Models\ServicoLembreteConfig;
use App\Models\User;
use App\Services\ServicoStatusService;
use App\Services\VerificacaoDiariaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Monthly servicos are first warned 7 days ahead, six-monthly/annual ones 30 days ahead. */
class AntecedenciaLembretesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-13 10:00:00');
        Mail::fake();

        Configuracao::create(['chave' => 'empresa', 'valor' => ['nome' => 'Teste', 'email' => 'empresa@teste.mz', 'moeda' => 'MZN']]);
        Configuracao::create(['chave' => 'lembretes', 'valor' => [
            'verificacao_diaria' => true, 'd30' => true, 'd15' => true, 'd7' => true, 'd3' => true, 'd1' => true, 'no_dia' => true, 'apos_vencimento' => true,
        ]]);
        Configuracao::create(['chave' => 'email_template_renovacao', 'valor' => ['assunto' => 'Vence [DATA]', 'corpo' => "Olá [NOME].\n\nVence em [DATA]."]]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function servico(string $periodicidade, string $vencimento, ?int $duracao = null): Servico
    {
        $cliente = Cliente::create(['nome' => 'Cliente', 'email' => 'c@teste.mz', 'status' => 'activo', 'desde' => '2026-01-01']);

        return Servico::create([
            'cliente_id' => $cliente->id, 'nome' => 'Serviço', 'categoria' => 'hospedagem', 'valor' => 1000,
            'periodo' => 'mes', 'periodicidade' => $periodicidade, 'duracao_dias' => $duracao,
            'inicio' => '2026-01-01', 'vencimento' => $vencimento, 'status' => 'activo',
        ]);
    }

    private function enviados(): array
    {
        return LembreteEnviado::orderBy('id')->pluck('intervalo')->map->value->all();
    }

    public function test_a_monthly_servico_is_not_warned_20_days_ahead(): void
    {
        $s = $this->servico('mensal', '2026-11-02'); // 20 days

        app(VerificacaoDiariaService::class)->executar();

        $this->assertSame([], $this->enviados());
        $this->assertSame(ServicoStatus::Activo, $s->fresh()->status);
    }

    public function test_a_monthly_servico_is_first_warned_7_days_ahead(): void
    {
        $s = $this->servico('mensal', '2026-10-20');

        app(VerificacaoDiariaService::class)->executar();

        $this->assertSame(['d7'], $this->enviados());
        $this->assertSame(ServicoStatus::AVencer, $s->fresh()->status);
    }

    public function test_annual_and_six_monthly_servicos_are_warned_30_days_ahead(): void
    {
        $anual = $this->servico('anual', '2026-11-07'); // 25 days
        $semestral = $this->servico('semestral', '2026-11-12'); // 30 days

        app(VerificacaoDiariaService::class)->executar();

        $this->assertSame(['d30', 'd30'], $this->enviados());
        $this->assertSame(ServicoStatus::AVencer, $anual->fresh()->status);
        $this->assertSame(ServicoStatus::AVencer, $semestral->fresh()->status);
    }

    public function test_custom_duration_follows_the_closest_cycle(): void
    {
        $status = app(ServicoStatusService::class);

        $this->assertSame(7, $status->antecedencia($this->servico('personalizada', '2026-11-20', 30)));
        $this->assertSame(30, $status->antecedencia($this->servico('personalizada', '2026-11-20', 365)));
        $this->assertNull($status->antecedencia($this->servico('unica', '2026-11-20')));
    }

    public function test_an_explicit_choice_on_the_servico_still_wins(): void
    {
        $s = $this->servico('mensal', '2026-11-02'); // 20 days
        ServicoLembreteConfig::create(['servico_id' => $s->id, 'd30' => true]);

        app(VerificacaoDiariaService::class)->executar();

        $this->assertSame(['d30'], $this->enviados());
    }

    public function test_the_lead_time_is_editable_in_configuracoes(): void
    {
        $this->actingAs(User::factory()->create());
        $s = $this->servico('mensal', '2026-10-18'); // 5 days

        Livewire::test(ConfiguracoesIndex::class)
            ->set('antecedencias.mensal', '3')
            ->assertHasNoErrors();

        $this->assertSame(3, Configuracao::obter('lembretes')['antecedencia']['mensal']);
        $this->assertSame(ServicoStatus::Activo, $s->fresh()->status, 'statuses follow the new lead time at once');

        app(VerificacaoDiariaService::class)->executar();
        $this->assertSame([], $this->enviados());
    }
}
