<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Time-based one-time codes (RFC 6238: SHA-1, 6 digits, 30-second steps) —
 * what Google Authenticator, Microsoft Authenticator, Authy etc. generate.
 */
class Totp
{
    private const ALFABETO = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIODO = 30;

    public static function gerarSegredo(): string
    {
        $bytes = random_bytes(20);
        $bits = '';
        foreach (str_split($bytes) as $b) {
            $bits .= str_pad(decbin(ord($b)), 8, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($grupo) => self::ALFABETO[bindec($grupo)], str_split($bits, 5)));
    }

    public static function passoActual(): int
    {
        return intdiv(time(), self::PERIODO);
    }

    public static function codigo(string $segredo, int $passo): string
    {
        $hash = hash_hmac('sha1', pack('N*', 0, $passo), self::decodificar($segredo), true);
        $offset = ord($hash[19]) & 0x0F;
        $numero = ((ord($hash[$offset]) & 0x7F) << 24) | (ord($hash[$offset + 1]) << 16) | (ord($hash[$offset + 2]) << 8) | ord($hash[$offset + 3]);

        return str_pad((string) ($numero % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    /**
     * The time step the code belongs to (one step either side tolerated for
     * a phone clock slightly off), or null. Steps at or before $ultimoPasso
     * are refused, so an intercepted code can't be replayed.
     */
    public static function verificar(string $segredo, string $codigo, ?int $ultimoPasso = null): ?int
    {
        $codigo = preg_replace('/\D/', '', $codigo);
        if (strlen($codigo) !== 6) {
            return null;
        }

        $agora = self::passoActual();
        foreach ([$agora - 1, $agora, $agora + 1] as $passo) {
            if (($ultimoPasso === null || $passo > $ultimoPasso) && hash_equals(self::codigo($segredo, $passo), $codigo)) {
                return $passo;
            }
        }

        return null;
    }

    public static function uri(string $segredo, string $conta): string
    {
        $emissor = config('app.name', 'Vencia');

        return 'otpauth://totp/'.rawurlencode($emissor.':'.$conta).'?secret='.$segredo.'&issuer='.rawurlencode($emissor).'&period='.self::PERIODO;
    }

    public static function qrSvg(string $uri): string
    {
        return (new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd)))->writeString($uri);
    }

    private static function decodificar(string $segredo): string
    {
        $bits = '';
        foreach (str_split(strtoupper(rtrim($segredo, '='))) as $c) {
            $bits .= str_pad(decbin(strpos(self::ALFABETO, $c)), 5, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($byte) => chr(bindec($byte)), array_filter(str_split($bits, 8), fn ($b) => strlen($b) === 8)));
    }
}
