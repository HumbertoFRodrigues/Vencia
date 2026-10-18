<?php

namespace App\Support;

/**
 * Tells a file's MIME type from its first bytes, for servers without PHP's
 * "fileinfo" extension (common on cPanel). Used by bootstrap/fileinfo.php.
 * Covers what Vencia receives: logos, payment proofs, CSV and backups.
 */
class TipoDeFicheiro
{
    public static function porConteudo(string $bytes): string
    {
        $inicio = substr($bytes, 0, 16);

        return match (true) {
            $bytes === '' => 'application/x-empty',
            str_starts_with($inicio, "\x89PNG\r\n\x1a\n") => 'image/png',
            str_starts_with($inicio, "\xFF\xD8\xFF") => 'image/jpeg',
            str_starts_with($inicio, 'GIF87a'), str_starts_with($inicio, 'GIF89a') => 'image/gif',
            str_starts_with($inicio, 'RIFF') && substr($inicio, 8, 4) === 'WEBP' => 'image/webp',
            str_starts_with($inicio, 'BM') => 'image/bmp',
            str_starts_with($inicio, '%PDF-') => 'application/pdf',
            str_starts_with($inicio, "PK\x03\x04") => 'application/zip',
            str_starts_with($inicio, "\x1F\x8B") => 'application/gzip',
            (bool) preg_match('/^\s*(<\?xml[^>]*>\s*)?(<!--.*?-->\s*)*(<!DOCTYPE svg[^>]*>\s*)?<svg[\s>]/is', substr($bytes, 0, 4096)) => 'image/svg+xml',
            ! preg_match('/[\x00-\x08\x0E-\x1F]/', substr($bytes, 0, 4096)) => 'text/plain',
            default => 'application/octet-stream',
        };
    }

    public static function porFicheiro(string $caminho): string|false
    {
        $h = @fopen($caminho, 'rb');
        if (! $h) {
            return false;
        }
        $bytes = (string) fread($h, 4096);
        fclose($h);

        return self::porConteudo($bytes);
    }
}
