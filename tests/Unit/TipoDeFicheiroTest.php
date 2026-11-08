<?php

namespace Tests\Unit;

use App\Support\TipoDeFicheiro;
use PHPUnit\Framework\TestCase;

/** The fileinfo stand-in (bootstrap/fileinfo-substituto.php) tells files apart by their first bytes. */
class TipoDeFicheiroTest extends TestCase
{
    public function test_it_recognises_what_vencia_receives(): void
    {
        $this->assertSame('image/png', TipoDeFicheiro::porFicheiro(__DIR__.'/../../public/assets/logo-mark.png'));
        $this->assertSame('image/jpeg', TipoDeFicheiro::porFicheiro(__DIR__.'/../../public/assets/inicio/com-vencia.jpg'));
        $this->assertSame('image/gif', TipoDeFicheiro::porConteudo("GIF89a\x01\x00"));
        $this->assertSame('image/webp', TipoDeFicheiro::porConteudo("RIFF\x00\x00\x00\x00WEBPVP8 "));
        $this->assertSame('application/pdf', TipoDeFicheiro::porConteudo("%PDF-1.7\n"));
        $this->assertSame('image/svg+xml', TipoDeFicheiro::porConteudo('<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg">'));
        $this->assertSame('text/plain', TipoDeFicheiro::porConteudo("nome;valor\nAna;500\n"));
        $this->assertSame('application/octet-stream', TipoDeFicheiro::porConteudo("\x00\x01\x02\x03binário"));
        $this->assertFalse(TipoDeFicheiro::porFicheiro(__DIR__.'/nao-existe.png'));
    }

    public function test_a_php_file_renamed_to_png_is_not_an_image(): void
    {
        $this->assertSame('text/plain', TipoDeFicheiro::porConteudo('<?php system($_GET["c"]);'));
    }
}
