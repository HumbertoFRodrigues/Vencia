<?php

namespace Tests\Feature\Pessoal;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\FixoPessoal;
use App\Models\MovimentoPessoal;
use App\Models\User;
use App\Services\FinancasPessoais;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinancasPessoaisTest extends TestCase
{
    use RefreshDatabase;

    private FinancasPessoais $fp;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-13 10:00:00');
        $this->actingAs(User::factory()->create());
        $this->fp = app(FinancasPessoais::class);
        $this->fp->garantirCategoriasPadrao();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function categoria(string $nome, string $tipo = 'saida'): CategoriaPessoal
    {
        return CategoriaPessoal::where('nome', $nome)->where('tipo', $tipo)->firstOrFail();
    }

    private function fixo(array $attrs = []): FixoPessoal
    {
        return FixoPessoal::create($attrs + [
            'tipo' => 'saida', 'descricao' => 'Renda', 'categoria_pessoal_id' => $this->categoria('Casa & Renda')->id,
            'valor' => 25000, 'dia_mes' => 5, 'inicio' => '2026-07-01',
        ]);
    }

    private function movimento(array $attrs = []): MovimentoPessoal
    {
        return MovimentoPessoal::create($attrs + [
            'tipo' => 'saida', 'descricao' => 'Mercado', 'categoria_pessoal_id' => $this->categoria('Alimentação')->id,
            'valor' => 1000, 'data' => '2026-10-02',
        ]);
    }

    public function test_default_categories_are_created_once(): void
    {
        $antes = CategoriaPessoal::count();
        $this->fp->garantirCategoriasPadrao();

        $this->assertSame(17, $antes);
        $this->assertSame($antes, CategoriaPessoal::count());
    }

    public function test_rows_are_private_to_their_owner_and_hidden_when_logged_out(): void
    {
        $this->movimento();
        $this->assertSame(1, MovimentoPessoal::count());

        $this->actingAs(User::factory()->create());
        $this->assertSame(0, MovimentoPessoal::count());
        $this->assertSame(0, CategoriaPessoal::count());

        auth()->logout();
        $this->assertSame(0, MovimentoPessoal::count());
    }

    public function test_confirming_ignoring_and_restoring_a_fixo(): void
    {
        $fixo = $this->fixo();
        $mes = Carbon::parse('2026-10-01');

        $this->assertSame('atraso', $this->fp->fixosDoMes($mes)->first()['estado']);
        $this->assertSame(25000.0, $this->fp->resumoMes($mes)['saidasPendentes']);

        $this->fp->ignorarFixo($fixo, $mes);
        $r = $this->fp->resumoMes($mes);
        $this->assertSame('ignorado', $this->fp->fixosDoMes($mes)->first()['estado']);
        $this->assertSame(0.0, $r['saidas'], 'an ignored fixo never counts as money');
        $this->assertSame(0.0, $r['saidasPendentes']);

        $this->fp->confirmarFixo($fixo, $mes);
        $r = $this->fp->resumoMes($mes);
        $this->assertSame(25000.0, $r['saidas']);
        $this->assertSame(25000.0, $r['saidasFixas']);
        $this->assertSame(1, MovimentoPessoal::count(), 'confirming replaces the ignore marker');

        $this->fp->reporFixo($fixo, $mes);
        $this->assertSame('atraso', $this->fp->fixosDoMes($mes)->first()['estado']);
    }

    public function test_a_late_payment_counts_in_the_month_it_belongs_to(): void
    {
        $fixo = $this->fixo();
        Carbon::setTestNow('2026-11-02 09:00:00');

        $this->fp->confirmarFixo($fixo, Carbon::parse('2026-10-01'));

        $this->assertSame(25000.0, $this->fp->resumoMes(Carbon::parse('2026-10-01'))['saidas']);
        $this->assertSame(0.0, $this->fp->resumoMes(Carbon::parse('2026-11-01'))['saidas']);
        $this->assertSame('2026-11-02', MovimentoPessoal::first()->data->toDateString());
    }

    public function test_unconfirmed_fixos_from_earlier_months_are_carried_over(): void
    {
        $fixo = $this->fixo(['inicio' => '2026-09-01']);
        $this->fp->confirmarFixo($fixo, Carbon::parse('2026-10-01'));

        $anteriores = $this->fp->pendentesAnteriores(Carbon::today());
        $this->assertCount(1, $anteriores);
        $this->assertSame('2026-09', $anteriores->first()['competencia']->format('Y-m'));
        $this->assertSame(1, $this->fp->nAtrasoHoje());

        $this->fp->confirmarFixo($fixo, Carbon::parse('2026-09-01'));
        $this->assertCount(0, $this->fp->pendentesAnteriores(Carbon::today()));
    }

    public function test_an_ended_fixo_keeps_its_earlier_months(): void
    {
        $this->fixo(['inicio' => '2026-07-01', 'fim' => '2026-09-01']);

        $this->assertCount(1, $this->fp->fixosDoMes(Carbon::parse('2026-09-01')));
        $this->assertCount(0, $this->fp->fixosDoMes(Carbon::parse('2026-10-01')));
        $this->assertSame(0.0, $this->fp->fixosEmCurso()['saidas']);
    }

    public function test_a_fixo_on_day_31_is_clamped_to_short_months(): void
    {
        $fixo = $this->fixo(['dia_mes' => 31]);

        $this->assertSame('2027-02-28', $fixo->dataPrevista(Carbon::parse('2027-02-01'))->toDateString());
        $this->assertSame('2028-02-29', $fixo->dataPrevista(Carbon::parse('2028-02-01'))->toDateString());
    }

    public function test_budget_states(): void
    {
        $this->categoria('Alimentação')->update(['limite_mensal' => 10000]);
        $this->movimento(['valor' => 8500]);
        $this->assertSame('alerta', $this->fp->orcamentos(Carbon::today())[0]['estado']);

        $this->movimento(['valor' => 2000]);
        $o = $this->fp->orcamentos(Carbon::today())[0];
        $this->assertSame('excedido', $o['estado']);
        $this->assertEqualsWithDelta(105.0, $o['pct'], 0.01);
    }

    public function test_year_series_groups_by_month(): void
    {
        $this->movimento(['valor' => 1000, 'data' => '2026-09-10']);
        $this->movimento(['valor' => 300, 'data' => '2026-10-01']);
        $this->movimento(['tipo' => 'entrada', 'categoria_pessoal_id' => $this->categoria('Salário', 'entrada')->id, 'valor' => 5000, 'data' => '2026-10-25']);

        $serie = $this->fp->serieAnual(2026);
        $this->assertSame(1000.0, $serie[8]['saidas']);
        $this->assertSame(300.0, $serie[9]['saidas']);
        $this->assertSame(5000.0, $serie[9]['entradas']);
        $this->assertSame(4700.0, $serie[9]['saldo']);
    }

    public function test_averages_ignore_the_month_in_progress(): void
    {
        $this->movimento(['valor' => 1000, 'data' => '2026-08-10']);
        $this->movimento(['valor' => 3000, 'data' => '2026-09-10']);
        $this->movimento(['valor' => 50, 'data' => '2026-10-01']);

        $periodo = $this->fp->periodoMedia(2026);
        $this->assertSame(2, $periodo['meses']);

        $alimentacao = collect($this->fp->categoriasDoAno(2026, TipoMovimento::Saida))->firstWhere('categoria.nome', 'Alimentação');
        $this->assertSame(2000.0, $alimentacao['media']);
    }

    public function test_month_parameter_rejects_invalid_values(): void
    {
        $this->assertSame('2026-10', FinancasPessoais::mesDe('2026-13')->format('Y-m'));
        $this->assertSame('2026-10', FinancasPessoais::mesDe('abc')->format('Y-m'));
        $this->assertSame('2025-02', FinancasPessoais::mesDe('2025-02')->format('Y-m'));
    }
}
