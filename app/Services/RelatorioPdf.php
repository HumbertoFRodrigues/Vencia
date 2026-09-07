<?php

namespace App\Services;

use App\Models\Configuracao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Builds the one shared "relatório" PDF (Dashboard/Pagamentos/Finanças
 * exports) through resources/views/pdf/relatorio.blade.php — same Dompdf
 * technique and money/date/logo conventions as ReciboController's receipt,
 * just parameterised for an arbitrary table instead of one fixed layout.
 * Callers gather/filter their own rows exactly as their exportarCsv() does,
 * format each cell to a display string, and hand them here.
 *
 * Returns a StreamedResponse rather than calling the Dompdf wrapper's own
 * ->download() (what ReciboController, a plain controller action, uses) —
 * confirmed by testing, not assumed: ->download() there returns a plain
 * Illuminate\Http\Response, and Livewire's action-call handler
 * (HandleComponents::callMethods()) only special-cases StreamedResponse/
 * BinaryFileResponse/RedirectResponse for a direct pass-through to the
 * browser. A plain Response returned from a Livewire action instead gets
 * shoved into the JSON "returns" payload, and its raw binary PDF bytes then
 * blow up json_encode with "Malformed UTF-8 characters". Streaming the same
 * rendered bytes through response()->streamDownload() — the exact mechanism
 * exportarCsv() already relies on — sends byte-identical PDF content as a
 * real attachment download and is one of the types Livewire does hand
 * straight through.
 */
class RelatorioPdf
{
    /**
     * @param  list<string>  $colunas  Column headers.
     * @param  list<list<string>>  $linhas  Row data, each cell already formatted for display.
     * @param  list<int>  $colunasNumericas  0-based indices of columns to right-align (money/counts).
     * @param  array<string, string>|null  $resumo  Optional label => formatted-value summary tiles shown above the table.
     */
    public static function gerar(
        string $titulo,
        string $subtitulo,
        array $colunas,
        array $linhas,
        string $nomeArquivo,
        array $colunasNumericas = [],
        ?array $resumo = null,
    ): StreamedResponse {
        $empresa = Configuracao::obter('empresa', []);

        $pdf = Pdf::loadView('pdf.relatorio', [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'colunas' => $colunas,
            'linhas' => $linhas,
            'colunasNumericas' => $colunasNumericas,
            'resumo' => $resumo,
            'empresa' => $empresa,
            'logoDataUri' => self::logoDataUri($empresa['logo_path'] ?? null),
            'geradoEm' => Carbon::now(),
        ])
            ->setPaper('a4')
            // Scoped to this Dompdf instance only (Options::set() on the
            // instance built inside loadView(), not a global config change) —
            // needed so the footer's "Página X de Y" script (evaluated via
            // Dompdf's own <script type="text/php"> mechanism) actually runs.
            // The template itself is ours, never user-supplied HTML, so this
            // carries none of the "untrusted document" risk the option's
            // doc-block warns about.
            ->setOption(['isPhpEnabled' => true]);

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $nomeArquivo, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /** Same pt-PT convention as the CSV exports: dot thousands, no decimals, trailing currency suffix. */
    public static function moeda(float $valor): string
    {
        return number_format($valor, 0, ',', '.').' '.\App\Models\Configuracao::moeda();
    }

    /**
     * The company's own uploaded logo wins when configured (Configurações →
     * Empresa); until then, falling back to the Vencia app mark keeps these
     * reports from looking bare/unbranded rather than showing nothing.
     */
    private static function logoDataUri(?string $logoPath): ?string
    {
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $conteudo = Storage::disk('public')->get($logoPath);
            $mime = Storage::disk('public')->mimeType($logoPath) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode($conteudo);
        }

        $fallback = public_path('assets/logo-mark.png');

        if (is_file($fallback)) {
            return 'data:image/png;base64,'.base64_encode(file_get_contents($fallback));
        }

        return null;
    }
}
