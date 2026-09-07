<?php

namespace App\Mail\Concerns;

use App\Models\Configuracao;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Storage;

/**
 * Shared HTML wrapper for every client-facing template email
 * (LembreteVencimentoMail, ServicoTerminadoMail) — one Blade view
 * (resources/views/emails/notificacao.blade.php) so both Mailables render
 * with the same header / kicker / summary-card / footer chrome instead of
 * each re-implementing table-based HTML.
 *
 * Only the *wrapper* is shared — the admin's own template text (already
 * placeholder-substituted by the caller) still flows through unchanged: this
 * trait only splits it into paragraphs for typographic hierarchy (greeting /
 * body / signature) and never rewrites its wording.
 */
trait ComLayoutDeEmail
{
    /**
     * @param  array<string, string>  $resumo  Label => already-formatted value, shown as a small bordered summary
     *                                          card — built from real model data (servico/valor/data), never parsed
     *                                          out of the free-text corpo, so it can't drift from what the corpo says.
     */
    protected function contentComLayout(string $kicker, string $corpo, array $resumo): Content
    {
        $partes = $this->dividirParagrafos($corpo);
        $empresa = Configuracao::obter('empresa', []);

        return new Content(view: 'emails.notificacao', with: [
            'empresaNome' => $empresa['nome'] ?? config('app.name'),
            'logoUrl' => $this->logoEmailUrl($empresa['logo_path'] ?? null),
            'kicker' => $kicker,
            'saudacao' => $partes['saudacao'],
            'paragrafos' => $partes['meio'],
            'resumo' => $resumo,
            'fecho' => $partes['fecho'],
        ]);
    }

    /**
     * Splits the (already placeholder-substituted) corpo into a greeting
     * paragraph, zero or more body paragraphs, and a closing/signature
     * paragraph — the same "\n\n"-separated paragraph convention the
     * template editor's textarea and LembreteVencimentoMail's
     * interval-context insertion already rely on. Degrades gracefully for a
     * template with fewer than 3 paragraphs (e.g. an admin who trims the
     * default text down) instead of erroring.
     *
     * @return array{saudacao: string, meio: list<string>, fecho: string}
     */
    private function dividirParagrafos(string $corpo): array
    {
        $partes = array_values(array_filter(
            array_map('trim', explode("\n\n", $corpo)),
            static fn (string $p): bool => $p !== '',
        ));

        if ($partes === []) {
            return ['saudacao' => '', 'meio' => [], 'fecho' => ''];
        }

        if (count($partes) === 1) {
            return ['saudacao' => $partes[0], 'meio' => [], 'fecho' => ''];
        }

        $saudacao = array_shift($partes);
        $fecho = array_pop($partes);

        return ['saudacao' => $saudacao, 'meio' => $partes, 'fecho' => $fecho];
    }

    /**
     * Emails cannot lean on `data:` URIs the way the PDF reports and the
     * receipt page do (Dompdf renders those internally, and a browser tab is
     * one trusted context) — a number of real-world mail clients strip or
     * refuse inline base64 images outright: Outlook desktop's Word-based
     * rendering engine and several corporate mail gateways do not render
     * `data:` image sources at all, while every mainstream client (Gmail,
     * iOS/Android Mail, Outlook.com, Apple Mail) fetches a plain https image
     * URL without issue (some proxy/cache it, a few prompt "display images"
     * — a well-understood, acceptable trade-off for transactional email).
     * So this links to a real public URL instead: the company's own
     * uploaded logo via the public disk's storage symlink, falling back to
     * the app's own asset() logo mark — the same fallback convention as
     * RelatorioPdf::logoDataUri(), just a URL instead of an embedded
     * data: URI.
     */
    private function logoEmailUrl(?string $logoPath): ?string
    {
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return Storage::disk('public')->url($logoPath);
        }

        return asset('assets/logo-mark.png');
    }
}
