<?php

namespace Tests\Feature\Pessoal;

use App\Enums\TipoMovimento;
use App\Livewire\Pessoal\Saldos;
use App\Models\CategoriaPessoal;
use App\Models\MetaPessoal;
use App\Models\MetodoPagamentoOpcao;
use App\Models\MovimentoPessoal;
use App\Models\User;
use App\Services\FinancasPessoais;
use App\Services\SaldosEMetas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SaldosEMetasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-14 10:00:00');
        $this->actingAs(User::factory()->create());
        foreach (['mpesa', 'transferencia', 'dinheiro'] as $m) {
            MetodoPagamentoOpcao::create(['nome' => $m, 'arquivado' => false]);
        }
        app(FinancasPessoais::class)->garantirCategoriasPadrao();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function mov(string $tipo, float $valor, ?string $meio): void
    {
        MovimentoPessoal::create([
            'tipo' => $tipo, 'descricao' => 'x', 'valor' => $valor, 'data' => '2026-10-02', 'meio' => $meio,
            'categoria_pessoal_id' => CategoriaPessoal::where('tipo', $tipo)->first()->id,
        ]);
    }

    private function saldo(string $meio): float
    {
        return collect(app(SaldosEMetas::class)->saldos())->firstWhere('meio', $meio)['saldo'];
    }

    public function test_balances_follow_the_movements_of_each_account(): void
    {
        $this->mov('entrada', 50000, 'transferencia');
        $this->mov('saida', 8000, 'transferencia');
        $this->mov('saida', 1200, 'mpesa');
        $this->mov('saida', 300, null);

        $this->assertSame(42000.0, $this->saldo('transferencia'));
        $this->assertSame(-1200.0, $this->saldo('mpesa'));
        $this->assertSame(-300.0, app(SaldosEMetas::class)->semMeio());
    }

    public function test_setting_the_real_balance_then_new_movements_keep_it_right(): void
    {
        $this->mov('saida', 1200, 'mpesa');

        Livewire::test(Saldos::class)->call('acertar', 'mpesa')->set('valorActual', '10.000')->call('guardarAcerto')->assertHasNoErrors();
        $this->assertSame(10000.0, $this->saldo('mpesa'));

        $this->mov('saida', 500, 'mpesa');
        $this->assertSame(9500.0, $this->saldo('mpesa'));
        $this->assertSame(1, MovimentoPessoal::where('meio', 'mpesa')->where('valor', '>', 600)->count(), 'no fake movement created');
    }

    public function test_a_goal_tracks_contributions_and_the_monthly_amount_needed(): void
    {
        Livewire::test(Saldos::class)
            ->call('abrirMeta')
            ->set('metaNome', 'Fundo de emergência')->set('metaValor', '100.000')->set('metaData', '2027-03')
            ->call('guardarMeta')->assertHasNoErrors();

        $meta = MetaPessoal::sole();
        app(SaldosEMetas::class)->contribuir($meta, 20000, Carbon::today(), 'mpesa');

        $linha = app(SaldosEMetas::class)->metas()[0];
        $this->assertSame(20000.0, $linha['guardado']);
        $this->assertSame(80000.0, $linha['falta']);
        $this->assertSame(6, $linha['meses']); // Oct..Mar
        $this->assertEqualsWithDelta(13333.33, $linha['porMes'], 0.01);

        $c = MovimentoPessoal::sole();
        $this->assertSame(TipoMovimento::Saida, $c->tipo);
        $this->assertSame('Poupança & Investimento', $c->categoria->nome);
        $this->assertSame(-20000.0, $this->saldo('mpesa'));
    }

    public function test_reaching_the_target_completes_the_goal_and_deleting_keeps_the_money(): void
    {
        $meta = MetaPessoal::create(['nome' => 'Férias', 'valor_alvo' => 5000]);

        Livewire::test(Saldos::class)->call('abrirContribuicao', $meta->id)->set('contribuicaoValor', '5000')->call('guardarContribuicao')->assertHasNoErrors();
        $this->assertNotNull($meta->fresh()->concluida_em);

        Livewire::test(Saldos::class)->call('abrirMeta', $meta->id)->call('apagarMeta');
        $this->assertSame(0, MetaPessoal::count());
        $this->assertSame(1, MovimentoPessoal::count());
    }

    public function test_page_renders(): void
    {
        MetaPessoal::create(['nome' => 'Carro', 'valor_alvo' => 300000, 'data_alvo' => '2027-12-31']);
        $this->get('/pessoal/saldos')->assertOk()->assertSee('Carro')->assertSee('Saldos por conta');
    }
}
