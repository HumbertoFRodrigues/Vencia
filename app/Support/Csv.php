<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * pt-PT CSV downloads (UTF-8 BOM so Excel reads accents, ';' separator)
 * shared by every export. Text cells that a spreadsheet would run as a
 * formula (= + - @, tab, CR) are prefixed with an apostrophe, so a client
 * or category named "=HYPERLINK(...)" is shown as text, never executed.
 */
class Csv
{
    /**
     * @param  list<string>  $cabecalho
     * @param  iterable<array<int, mixed>>  $linhas
     */
    public static function download(string $nomeArquivo, array $cabecalho, iterable $linhas): StreamedResponse
    {
        return response()->streamDownload(function () use ($cabecalho, $linhas): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $cabecalho, ';');

            foreach ($linhas as $linha) {
                fputcsv($handle, array_map([self::class, 'celula'], $linha), ';');
            }

            fclose($handle);
        }, $nomeArquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function celula(mixed $valor): string
    {
        $texto = (string) ($valor ?? '');

        if ($texto !== '' && ! is_numeric($texto) && in_array($texto[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$texto;
        }

        return $texto;
    }
}
