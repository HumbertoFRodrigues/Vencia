<?php

namespace Tests\Feature\Negocio;

use App\Enums\TipoMovimento;
use App\Livewire\Financas\FinancasIndex;
use App\Livewire\Financas\RetirarDialog;
use App\Livewire\Pessoal\MovimentoDialog;
use App\Models\Cliente;
use App\Models\MovimentoPessoal;
use App\Models\Pagamento;
use App\Models\Retirada;
use App\Models\Servico;
use App\Models\User;
use App\Services\Retiradas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RetiradasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());

        $cliente = Cliente::create(['nome' => 'C', 'email' => 'c@t.mz', 'status' => 'activo', 'desde' => '2026-01-01']);
        $servico = Servico::create([
            'cliente_id' => $cliente->id, 'nome' => 'S', 'categoria' => 'hospedagem', 'valor' => 50000,
            'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '2026-01-01', 'vencimento' => '2026-12-01', 'status' => 'activo',
        ]);
        Pagamento::create(['data' => now()->toDateString(), 'cliente_id' => $cliente->id, 'servico_id' => $servico->id, 'valor' => 50000, 'metodo' => 'mpesa', 'periodo' => '1 mês']);
    }

    public function test_withdrawing_creates_the_personal_entrada_and_lowers_what_is_available(): void
    {
        Livewire::test(RetirarDialog::class)
            ->call('abrir')
            ->set('valor', '15.000')
            ->set('nota', 'Salário de Outubro')
            ->call('confirmar')
            ->assertHasNoErrors();

        $retirada = Retirada::sole();
        $this->assertSame('15000.00', $retirada->valor);
        $this->assertSame(35000.0, app(Retiradas::class)->disponivel());

        $entrada = MovimentoPessoal::sole();
        $this->assertSame(TipoMovimento::Entrada, $entrada->tipo);
        $this->assertSame('Retirada da empresa', $entrada->categoria->nome);
        $this->assertSame($retirada->id, $entrada->retirada_id);
    }

    public function test_cancelling_removes_it_from_both_panels(): void
    {
        $retirada = app(Retiradas::class)->retirar(10000, now(), 'mpesa', null);

        Livewire::test(FinancasIndex::class)->call('anularRetirada', $retirada->id);

        $this->assertSame(0, Retirada::count());
        $this->assertSame(0, MovimentoPessoal::count());
        $this->assertSame(50000.0, app(Retiradas::class)->disponivel());
    }

    public function test_the_personal_entrada_cannot_be_edited_or_deleted_from_the_personal_panel(): void
    {
        app(Retiradas::class)->retirar(10000, now(), null, null);
        $entrada = MovimentoPessoal::sole();

        Livewire::test(MovimentoDialog::class)
            ->call('abrir', null, $entrada->id)
            ->assertSet('daEmpresa', true)
            ->set('valor', '1')
            ->call('guardar')
            ->call('apagar');

        $this->assertSame('10000.00', $entrada->fresh()->valor);
    }

    public function test_invalid_amounts_are_rejected(): void
    {
        Livewire::test(RetirarDialog::class)->call('abrir')->set('valor', '0')->call('confirmar')->assertHasErrors('valor');
        Livewire::test(RetirarDialog::class)->call('abrir')->set('valor', 'abc')->call('confirmar')->assertHasErrors('valor');
        $this->assertSame(0, Retirada::count());
    }
}
