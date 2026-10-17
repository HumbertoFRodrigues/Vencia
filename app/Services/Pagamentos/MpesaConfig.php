<?php

namespace App\Services\Pagamentos;

use App\Models\Configuracao;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Where the M-Pesa keys come from. Pasted in Admin → Plataforma (stored
 * encrypted with APP_KEY, like the SMTP password) or, failing that, from
 * .env (config/pagamentos.php). The panel wins when it has keys.
 */
class MpesaConfig
{
    /**
     * @return array{ligado: bool, api_key: ?string, public_key: ?string, service_provider_code: ?string, sandbox: bool, timeout: int, origem: string}
     */
    public static function get(): array
    {
        $painel = Configuracao::plataforma('mpesa', []);
        $timeout = (int) config('pagamentos.mpesa.timeout', 150);

        if (filled($painel['api_key'] ?? null)) {
            return [
                'ligado' => (bool) ($painel['ligado'] ?? false),
                'api_key' => self::decifrar($painel['api_key']),
                'public_key' => self::decifrar($painel['public_key'] ?? null),
                'service_provider_code' => $painel['service_provider_code'] ?? null,
                'sandbox' => (bool) ($painel['sandbox'] ?? true),
                'timeout' => $timeout,
                'origem' => 'painel',
            ];
        }

        return [
            'ligado' => config('pagamentos.gateway') === 'mpesa',
            'api_key' => config('pagamentos.mpesa.api_key'),
            'public_key' => config('pagamentos.mpesa.public_key'),
            'service_provider_code' => config('pagamentos.mpesa.service_provider_code'),
            'sandbox' => (bool) config('pagamentos.mpesa.sandbox', true),
            'timeout' => $timeout,
            'origem' => 'env',
        ];
    }

    public static function completo(): bool
    {
        $c = self::get();

        return filled($c['api_key']) && filled($c['public_key']) && filled($c['service_provider_code']);
    }

    /** On and with all three keys: "Pagar já com M-Pesa" shows on the Plano page. */
    public static function activo(): bool
    {
        return self::get()['ligado'] && self::completo();
    }

    /**
     * Saves what was pasted in the panel. A blank key keeps the one already saved.
     *
     * @param  array{ligado: bool, api_key: ?string, public_key: ?string, service_provider_code: ?string, sandbox: bool}  $dados
     */
    public static function guardar(array $dados): void
    {
        $actual = Configuracao::plataforma('mpesa', []);

        Configuracao::guardarPlataforma('mpesa', [
            'ligado' => $dados['ligado'],
            'sandbox' => $dados['sandbox'],
            'service_provider_code' => trim((string) $dados['service_provider_code']) ?: ($actual['service_provider_code'] ?? null),
            'api_key' => filled($dados['api_key']) ? Crypt::encryptString(trim($dados['api_key'])) : ($actual['api_key'] ?? null),
            // The portal shows the public key wrapped over several lines: one line, no spaces.
            'public_key' => filled($dados['public_key'])
                ? Crypt::encryptString(preg_replace('/-----[^-]+-----|\s+/', '', $dados['public_key']))
                : ($actual['public_key'] ?? null),
        ]);
    }

    private static function decifrar(?string $guardado): ?string
    {
        if (! filled($guardado)) {
            return null;
        }

        try {
            return Crypt::decryptString($guardado);
        } catch (DecryptException) {
            return null; // APP_KEY changed since: the keys have to be pasted again
        }
    }
}
