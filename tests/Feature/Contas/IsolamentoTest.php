<?php

namespace Tests\Feature\Contas;

use App\Enums\Papel;
use App\Livewire\Clientes\ClientesIndex;
use App\Livewire\Pagamentos\RegistrarPagamentoDialog;
use App\Livewire\Shared\GlobalSearch;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Conta;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Models\User;
use App\Services\Recibos;
use App\Services\Totais;
use App\Support\ContaActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** One business must never see, open, change or count another business's data. */
class IsolamentoTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $clienteA;

    private Servico $servicoA;

    private Pagamento $pagamentoA;

    private Conta $contaB;

    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        // Conta A (the test's default conta) with a client, service and payment.
        User::factory()->create();
        MetodoPagamentoOpcao::create(['nome' => 'mpesa', 'arquivado' => false]);
        Configuracao::create(['chave' => 'empresa', 'valor' => ['nome' => 'Empresa A', 'moeda' => 'MZN']]);
        $this->clienteA = Cliente::create(['nome' => 'Cliente Secreto A', 'email' => 'a@cliente.mz', 'status' => 'activo', 'desde' => '2026-01-01']);
        $this->servicoA = Servico::create([
            'cliente_id' => $this->clienteA->id, 'nome' => 'Servico Secreto A', 'categoria' => 'hospedagem', 'valor' => 9999,
            'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '2026-01-01', 'vencimento' => now()->addDays(3)->toDateString(),
            'status' => 'activo', 'metodo_habitual' => 'mpesa',
        ]);
        $this->pagamentoA = Pagamento::create(['data' => now()->toDateString(), 'cliente_id' => $this->clienteA->id, 'servico_id' => $this->servicoA->id, 'valor' => 9999, 'metodo' => 'mpesa', 'periodo' => '01/2026']);

        // Conta B, a different business with its own dono.
        $this->contaB = $this->outraConta('Empresa B');
        ContaActual::definir($this->contaB);
        $this->userB = User::factory()->create();
        MetodoPagamentoOpcao::create(['nome' => 'mpesa', 'arquivado' => false]);
    }

    private function entrarComoB(): void
    {
        $this->actingAs($this->userB);
        ContaActual::definir($this->contaB);
    }

    public function test_models_only_return_the_current_contas_rows(): void
    {
        $this->entrarComoB();

        $this->assertSame(0, Cliente::count());
        $this->assertSame(0, Servico::count());
        $this->assertSame(0, Pagamento::count());
        $this->assertNull(Cliente::find($this->clienteA->id));
        $totais = app(Totais::class)->calcular();
        $this->assertSame(0.0, (float) $totais['entradas']);
        $this->assertSame(0, $totais['servicosActivos']);
    }

    public function test_detail_pages_and_receipts_of_another_conta_are_not_found(): void
    {
        $this->actingAs($this->userB);

        $this->get('/clientes/'.$this->clienteA->id)->assertNotFound();
        $this->get('/servicos/'.$this->servicoA->id)->assertNotFound();
        $this->get('/pagamentos/'.$this->pagamentoA->id.'/recibo')->assertNotFound();
    }

    public function test_lists_and_search_do_not_show_another_contas_data(): void
    {
        $this->actingAs($this->userB);
        $this->get('/clientes')->assertOk()->assertDontSee('Cliente Secreto A');
        $this->get('/servicos')->assertOk()->assertDontSee('Servico Secreto A');
        $this->get('/pagamentos')->assertOk()->assertDontSee('Cliente Secreto A');

        $this->entrarComoB();
        Livewire::test(ClientesIndex::class)->assertDontSee('Cliente Secreto A');
        Livewire::test(GlobalSearch::class)->set('q', 'Secreto')->assertDontSee('Cliente Secreto A')->assertDontSee('Servico Secreto A');
    }

    public function test_ids_of_another_conta_are_rejected_in_forms(): void
    {
        $this->entrarComoB();

        Livewire::test(RegistrarPagamentoDialog::class)
            ->call('abrir')
            ->set('clienteSelecionado', (string) $this->clienteA->id)
            ->set('servicoSelecionado', (string) $this->servicoA->id)
            ->set('valor', '100')
            ->call('confirmar')
            ->assertHasErrors(['clienteSelecionado']);

        ContaActual::definir($this->conta);
        $this->assertSame(1, Pagamento::count(), 'nothing was added to conta A');
    }

    public function test_the_same_client_email_can_exist_in_two_contas(): void
    {
        $this->entrarComoB();

        $cliente = Cliente::create(['nome' => 'Outro', 'email' => 'a@cliente.mz', 'status' => 'activo', 'desde' => '2026-01-01']);

        $this->assertSame($this->contaB->id, $cliente->conta_id);
    }

    public function test_receipts_are_numbered_per_conta(): void
    {
        $this->entrarComoB();
        $cliente = Cliente::create(['nome' => 'B', 'email' => 'b@cliente.mz', 'status' => 'activo', 'desde' => '2026-01-01']);
        $servico = Servico::create([
            'cliente_id' => $cliente->id, 'nome' => 'S', 'categoria' => 'hospedagem', 'valor' => 10,
            'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '2026-01-01', 'vencimento' => now()->toDateString(), 'status' => 'activo',
        ]);
        $pagamento = Pagamento::create(['data' => now()->toDateString(), 'cliente_id' => $cliente->id, 'servico_id' => $servico->id, 'valor' => 10, 'metodo' => 'mpesa', 'periodo' => '01/2026']);

        $this->assertSame('#000001', Recibos::referencia($this->pagamentoA));
        $this->assertSame('#000001', Recibos::referencia($pagamento), 'conta B starts its own sequence');
        $this->assertSame($this->userB->id, $pagamento->user_id, 'who recorded it is kept');
    }

    public function test_settings_are_per_conta(): void
    {
        $this->entrarComoB();
        Configuracao::guardar('empresa', ['nome' => 'Empresa B', 'moeda' => 'USD']);

        $this->assertSame('USD', Configuracao::moeda());
        ContaActual::definir($this->conta);
        $this->assertSame('MZN', Configuracao::moeda());
        $this->assertSame('Empresa A', Configuracao::obter('empresa')['nome']);
    }

    public function test_a_registos_member_cannot_open_finance_history_or_settings(): void
    {
        $registos = User::factory()->papel(Papel::Registos, $this->contaB)->create();
        $this->actingAs($registos);

        $this->get('/clientes')->assertOk();
        $this->get('/financas')->assertForbidden();
        $this->get('/historico')->assertForbidden();
        $this->get('/configuracoes')->assertForbidden();
    }

    public function test_nothing_is_visible_outside_a_conta(): void
    {
        ContaActual::definir(null);

        $this->assertSame(0, Cliente::count(), 'fails closed: no conta, no rows');
    }

    public function test_a_suspended_conta_only_sees_its_notice(): void
    {
        $this->contaB->update(['estado' => Conta::SUSPENSA, 'motivo_suspensao' => 'Falta de pagamento']);
        $this->actingAs($this->userB);

        $this->get('/clientes')->assertForbidden()->assertSee('Falta de pagamento');
    }

    public function test_a_blocked_user_is_logged_out(): void
    {
        $this->userB->forceFill(['bloqueado_em' => now()])->save();
        $this->actingAs($this->userB);

        $this->get('/clientes')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
