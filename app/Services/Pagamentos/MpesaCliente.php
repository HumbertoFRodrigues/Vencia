<?php

namespace App\Services\Pagamentos;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Vodacom M-Pesa Open API, C2B single stage: the customer gets a PIN prompt
 * on their phone and the call returns once they confirm (or it times out).
 * Ported from the payment-api project, on Laravel's HTTP client.
 */
class MpesaCliente
{
    public static function configurado(): bool
    {
        return filled(config('pagamentos.mpesa.api_key'))
            && filled(config('pagamentos.mpesa.public_key'))
            && filled(config('pagamentos.mpesa.service_provider_code'));
    }

    /** "84 123 4567", "+258841234567" → "258841234567"; null when it isn't an M-Pesa (84/85) number. */
    public static function normalizarTelefone(string $telefone): ?string
    {
        $digitos = preg_replace('/\D+/', '', $telefone);

        return preg_match('/^(?:258)?(8[45]\d{7})$/', $digitos, $m) ? '258'.$m[1] : null;
    }

    /**
     * @return array{http: int, codigo: ?string, transaccao: ?string, descricao: ?string}
     */
    public function c2b(string $telefone, float $valor, string $referencia, string $referenciaTerceiro): array
    {
        $sandbox = (bool) config('pagamentos.mpesa.sandbox');
        $url = 'https://'.($sandbox ? 'api.sandbox.vm.co.mz' : 'api.vm.co.mz').':18352/ipg/v1x/c2bPayment/singleStage/';

        $resposta = Http::withToken($this->bearer())
            ->withHeaders(['Origin' => 'developer.mpesa.vm.co.mz'])
            ->withOptions(['verify' => ! $sandbox])
            ->connectTimeout(15)
            ->timeout((int) config('pagamentos.mpesa.timeout', 150))
            ->post($url, [
                'input_TransactionReference' => $referencia,
                'input_CustomerMSISDN' => $telefone,
                'input_Amount' => $this->valor($valor),
                'input_ThirdPartyReference' => $referenciaTerceiro,
                'input_ServiceProviderCode' => (string) config('pagamentos.mpesa.service_provider_code'),
            ]);

        $json = $resposta->json() ?? [];

        return [
            'http' => $resposta->status(),
            'codigo' => $json['output_ResponseCode'] ?? null,
            'transaccao' => $json['output_TransactionID'] ?? null,
            'descricao' => $json['output_ResponseDesc'] ?? null,
        ];
    }

    public static function mensagemErro(?string $codigo): string
    {
        return match ($codigo) {
            'INS-2' => 'O pagamento automático está mal configurado. Usa o pagamento manual abaixo.',
            'INS-5' => 'Pagamento cancelado: o PIN não foi confirmado.',
            'INS-6' => 'A transacção falhou. Tenta outra vez.',
            'INS-9' => 'O pedido expirou antes de confirmares o PIN. Tenta outra vez.',
            'INS-10' => 'Pagamento duplicado. Se já pagaste, aguarda a confirmação.',
            'INS-2006' => 'Saldo insuficiente na carteira M-Pesa.',
            'INS-2051' => 'Número de telemóvel inválido para M-Pesa.',
            null => 'Sem resposta do M-Pesa. Tenta outra vez ou usa o pagamento manual.',
            default => 'O M-Pesa recusou o pagamento ('.$codigo.'). Tenta outra vez ou usa o pagamento manual.',
        };
    }

    /** "500" for whole amounts, "499.50" otherwise. */
    private function valor(float $valor): string
    {
        return $valor == floor($valor) ? (string) (int) $valor : number_format($valor, 2, '.', '');
    }

    /** The API key encrypted with Vodacom's public key: the bearer token. */
    private function bearer(): string
    {
        $limpa = preg_replace('/\s+/', '', (string) config('pagamentos.mpesa.public_key'));
        $pem = "-----BEGIN PUBLIC KEY-----\n".chunk_split($limpa, 64, "\n")."-----END PUBLIC KEY-----\n";

        $chave = openssl_pkey_get_public($pem);
        if ($chave === false) {
            throw new RuntimeException('M-Pesa: a chave pública (MPESA_PUBLIC_KEY) é inválida.');
        }

        $cifrada = '';
        if (! openssl_public_encrypt((string) config('pagamentos.mpesa.api_key'), $cifrada, $chave, OPENSSL_PKCS1_PADDING)) {
            throw new RuntimeException('M-Pesa: não foi possível cifrar a MPESA_API_KEY.');
        }

        return base64_encode($cifrada);
    }
}
