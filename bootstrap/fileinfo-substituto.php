<?php

// Loaded by bootstrap/fileinfo.php only when PHP has no fileinfo extension.

use App\Support\TipoDeFicheiro;

foreach (['FILEINFO_NONE' => 0, 'FILEINFO_MIME_TYPE' => 16, 'FILEINFO_MIME_ENCODING' => 1024, 'FILEINFO_MIME' => 1040, 'FILEINFO_EXTENSION' => 16777216] as $nome => $valor) {
    defined($nome) || define($nome, $valor);
}

class finfo
{
    public function __construct(private int $flags = FILEINFO_NONE, ?string $magicDatabase = null) {}

    public function file(string $filename, int $flags = FILEINFO_NONE, $context = null): string|false
    {
        $tipo = TipoDeFicheiro::porFicheiro($filename);

        return $tipo === false ? false : $this->formatar($tipo, $flags ?: $this->flags);
    }

    public function buffer(string $string, int $flags = FILEINFO_NONE, $context = null): string|false
    {
        return $this->formatar(TipoDeFicheiro::porConteudo($string), $flags ?: $this->flags);
    }

    public function set_flags(int $flags): bool
    {
        $this->flags = $flags;

        return true;
    }

    private function formatar(string $tipo, int $flags): string
    {
        $texto = str_starts_with($tipo, 'text/') || $tipo === 'image/svg+xml';

        return match (true) {
            ($flags & FILEINFO_MIME) === FILEINFO_MIME => $tipo.'; charset='.($texto ? 'utf-8' : 'binary'),
            (bool) ($flags & FILEINFO_MIME_ENCODING) => $texto ? 'utf-8' : 'binary',
            default => $tipo,
        };
    }
}

function finfo_open(int $flags = FILEINFO_NONE, ?string $magic_database = null): finfo
{
    return new finfo($flags, $magic_database);
}

function finfo_file(finfo $finfo, string $filename, int $flags = FILEINFO_NONE, $context = null): string|false
{
    return $finfo->file($filename, $flags, $context);
}

function finfo_buffer(finfo $finfo, string $string, int $flags = FILEINFO_NONE, $context = null): string|false
{
    return $finfo->buffer($string, $flags, $context);
}

function finfo_set_flags(finfo $finfo, int $flags): bool
{
    return $finfo->set_flags($flags);
}

function finfo_close(finfo $finfo): bool
{
    return true;
}

function mime_content_type($filename): string|false
{
    return is_string($filename) ? TipoDeFicheiro::porFicheiro($filename) : false;
}
