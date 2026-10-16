<?php

namespace Tests\Feature\Contas;

use App\Contracts\GatewayPagamento;
use App\Livewire\Conta\Plano as PlanoPage;
use App\Models\Fatura;
use App\Models\Plano;
use App\Models\User;
use App\Services\Faturacao;
use App\Services\Pagamentos\GatewayManual;
use App\Services\Pagamentos\GatewayMpesa;
use App\Services\Pagamentos\MpesaCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** "Pagar já com M-Pesa", against a faked Vodacom API. */
class MpesaTest extends TestCase
{
    use RefreshDatabase;

    /** A throwaway RSA public key, in the format the Vodacom portal gives (base64 body, no PEM header). */
    private const CHAVE_PUBLICA_DE_TESTE = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+iNZZSOJvDsIoLen4cSzbQarZpBuBrnyIWINj0kcD2q6AS9B29LWZYd8skswJ9xDL1JqHiEI0rERTxYVJDuySr4t1sSj18BPZBZhFMuw6CGOotyPT+MXOuNOz8GoZ7XFP4ia0TQ0w19BSCHUPosq2A5K6+6qlxN/fVIZJrKwiOwIDAQAB';

    private Fatura $fatura;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        config([
            'pagamentos.gateway' => 'mpesa',
            'pagamentos.mpesa.api_key' => 'chave-de-teste',
            'pagamentos.mpesa.public_key' => self::CHAVE_PUBLICA_DE_TESTE,
            'pagamentos.mpesa.service_provider_code' => '171717',
            'pagamentos.mpesa.sandbox' => true,
        ]);

        $this->conta->update(['plano_id' => Plano::gratis()->id, 'plano_ate' => null]);
        $this->actingAs(User::factory()->create());
        $this->fatura = app(Faturacao::class)->pedir($this->conta, Plano::query()->where('slug', 'pro')->sole(), 'mensal', auth()->user());
    }

    public function test_the_gateway_is_only_on_with_keys_and_the_switch(): void
    {
        $this->assertInstanceOf(GatewayMpesa::class, app(GatewayPagamento::class));

        config(['pagamentos.mpesa.api_key' => null]);
        $this->assertInstanceOf(GatewayManual::class, app(GatewayPagamento::class));

        config(['pagamentos.mpesa.api_key' => 'x', 'pagamentos.gateway' => 'manual']);
        $this->assertInstanceOf(GatewayManual::class, app(GatewayPagamento::class));
    }

    public function test_phone_numbers_are_normalised(): void
    {
        $this->assertSame('258841234567', MpesaCliente::normalizarTelefone('84 123 4567'));
        $this->assertSame('258851234567', MpesaCliente::normalizarTelefone('+258 85 123 4567'));
        $this->assertNull(MpesaCliente::normalizarTelefone('82 123 4567'), 'not an M-Pesa (Vodacom) number');
        $this->assertNull(MpesaCliente::normalizarTelefone('1234'));
    }

    public function test_a_confirmed_payment_turns_pro_on_by_itself(): void
    {
        Http::fake(['*vm.co.mz*' => Http::response(['output_ResponseCode' => 'INS-0', 'output_TransactionID' => 'TX123ABC', 'output_ResponseDesc' => 'Request processed successfully'], 201)]);

        Livewire::test(PlanoPage::class)->set('telefone', '84 123 4567')->call('pagarMpesa')
            ->assertHasNoErrors()->assertDispatched('toast', fn ($n, $p) => $p['title'] === 'Pagamento confirmado');

        $fatura = $this->fatura->fresh();
        $this->assertSame(Fatura::PAGA, $fatura->estado);
        $this->assertSame('TX123ABC', $fatura->gateway_ref);
        $this->assertSame('pro', $this->conta->fresh()->planoEfectivo()->slug);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'api.sandbox.vm.co.mz:18352/ipg/v1x/c2bPayment/singleStage/')
            && $r['input_CustomerMSISDN'] === '258841234567'
            && $r['input_Amount'] === '500'
            && $r['input_ServiceProviderCode'] === '171717'
            && str_starts_with($r->header('Authorization')[0], 'Bearer '));
    }

    public function test_a_refused_payment_explains_why_and_changes_nothing(): void
    {
        Http::fake(['*vm.co.mz*' => Http::response(['output_ResponseCode' => 'INS-2006', 'output_ResponseDesc' => 'Insufficient balance'], 422)]);

        Livewire::test(PlanoPage::class)->set('telefone', '841234567')->call('pagarMpesa')
            ->assertHasErrors(['telefone']);

        $this->assertSame(Fatura::PENDENTE, $this->fatura->fresh()->estado);
        $this->assertSame('gratis', $this->conta->fresh()->planoEfectivo()->slug);
        $this->assertSame('Saldo insuficiente na carteira M-Pesa.', MpesaCliente::mensagemErro('INS-2006'));
    }

    public function test_no_connection_falls_back_to_the_manual_payment(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        Livewire::test(PlanoPage::class)->set('telefone', '841234567')->call('pagarMpesa')->assertHasErrors(['telefone']);

        $this->assertSame(Fatura::PENDENTE, $this->fatura->fresh()->estado);
    }

    public function test_the_plan_page_offers_mpesa_only_when_it_is_on(): void
    {
        Livewire::test(PlanoPage::class)->assertSee('Pagar já com M-Pesa');

        config(['pagamentos.gateway' => 'manual']);
        Livewire::test(PlanoPage::class)->assertDontSee('Pagar já com M-Pesa');
    }
}
