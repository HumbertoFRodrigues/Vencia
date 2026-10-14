<?php

namespace Tests\Feature\Negocio;

use App\Livewire\Pagamentos\RegistrarPagamentoDialog;
use App\Livewire\Servicos\RenovarDialog;
use App\Mail\ReciboPagamentoMail;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class RecibosTest extends TestCase
{
    use RefreshDatabase;

    private Servico $servico;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->actingAs(User::factory()->create());
        MetodoPagamentoOpcao::create(['nome' => 'mpesa', 'arquivado' => false]);
        Configuracao::create(['chave' => 'empresa', 'valor' => ['nome' => 'Comusanas', 'email' => 'empresa@t.mz', 'moeda' => 'MZN']]);
        Configuracao::create(['chave' => 'lembretes', 'valor' => []]);

        $cliente = Cliente::create(['nome' => 'Maria', 'email' => 'maria@t.mz', 'status' => 'activo', 'desde' => '2026-01-01']);
        $this->servico = Servico::create([
            'cliente_id' => $cliente->id, 'nome' => 'Hospedagem', 'categoria' => 'hospedagem', 'valor' => 1500,
            'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '2026-01-01', 'vencimento' => now()->addDays(3)->toDateString(),
            'status' => 'activo', 'metodo_habitual' => 'mpesa',
        ]);
    }

    public function test_renewing_emails_the_numbered_receipt_to_the_client(): void
    {
        Livewire::test(RenovarDialog::class)->call('abrir', $this->servico->id)->call('confirmar')->assertHasNoErrors();

        $pagamento = Pagamento::sole();
        Mail::assertSent(ReciboPagamentoMail::class, function (ReciboPagamentoMail $m) use ($pagamento) {
            return $m->hasTo('maria@t.mz') && $m->hasBcc('empresa@t.mz')
                && $m->pagamento->is($pagamento)
                && count($m->attachments()) === 1;
        });
        $this->assertSame('Recibo enviado', Historico::where('kind', 'email')->value('title'));
    }

    public function test_the_receipt_can_be_turned_off(): void
    {
        Livewire::test(RenovarDialog::class)->call('abrir', $this->servico->id)->set('enviarRecibo', false)->call('confirmar');

        $this->assertSame(1, Pagamento::count());
        Mail::assertNothingSent();
    }

    public function test_recording_a_payment_also_sends_it(): void
    {
        Livewire::test(RegistrarPagamentoDialog::class, ['servicoId' => $this->servico->id])
            ->call('abrir')
            ->call('confirmar')
            ->assertHasNoErrors();

        Mail::assertSent(ReciboPagamentoMail::class);
    }

    public function test_receipt_pdf_still_downloads(): void
    {
        Livewire::test(RenovarDialog::class)->call('abrir', $this->servico->id)->set('enviarRecibo', false)->call('confirmar');

        $resposta = $this->get('/pagamentos/'.Pagamento::sole()->id.'/recibo');
        $resposta->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $resposta->getContent());
    }
}
