<?php

namespace App\Support;

use App\Models\Configuracao;

/**
 * Google AdSense on the public pages (home, Termos, Privacidade), set in
 * Admin → Plataforma. Only the publisher ID is stored, never pasted HTML, so
 * nothing but Google's own script can end up on the page. Auto ads: Google
 * decides where the ads go. Never shown inside the app.
 */
class Anuncios
{
    /** "ca-pub-" followed by the 16 digits Google gives. */
    public const FORMATO = '/^ca-pub-\d{10,20}$/';

    /** @return array{ligado: bool, cliente: string} */
    public static function get(): array
    {
        $a = Configuracao::plataforma('anuncios', []);

        return ['ligado' => (bool) ($a['ligado'] ?? false), 'cliente' => (string) ($a['cliente'] ?? '')];
    }

    /** The publisher ID when ads are on, otherwise null. */
    public static function cliente(): ?string
    {
        $a = self::get();

        return $a['ligado'] && preg_match(self::FORMATO, $a['cliente']) ? $a['cliente'] : null;
    }

    /** Accepts "ca-pub-123…", "pub-123…" or the whole <script> Google shows, and keeps just the ID. */
    public static function extrairCliente(string $colado): string
    {
        return preg_match('/(?:ca-)?pub-(\d{10,20})/', $colado, $m) ? 'ca-pub-'.$m[1] : trim($colado);
    }

    /** Body of /ads.txt, which Google checks before it shows ads. */
    public static function adsTxt(): ?string
    {
        $cliente = self::cliente();

        return $cliente ? 'google.com, '.substr($cliente, 3).", DIRECT, f08c47fec0942fa0\n" : null;
    }
}
