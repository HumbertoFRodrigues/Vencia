<?php

namespace Tests\Feature\Contas;

use App\Livewire\Admin\Plataforma;
use App\Mail\TransportePhpMail;
use App\Models\Configuracao;
use App\Models\User;
use App\Services\EmailsDeTeste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

/** "Função mail() do servidor": emails with no SMTP account. */
class EnvioPhpMailTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array{para: string, assunto: string, corpo: string, cabecalhos: string, parametros: string}> */
    public static array $enviados = [];

    public static bool $falhar = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::$enviados = [];
        self::$falhar = false;

        // Windows' PHP can't send mail: record what mail() would get instead.
        Mail::extend('phpmail', fn () => new class extends TransportePhpMail
        {
            protected function mail(string $para, string $assunto, string $corpo, string $cabecalhos, string $parametros): bool
            {
                EnvioPhpMailTest::$enviados[] = compact('para', 'assunto', 'corpo', 'cabecalhos', 'parametros');

                return ! EnvioPhpMailTest::$falhar;
            }
        });

        $this->actingAs(User::factory()->create(['super_admin' => true]));
    }

    public function test_choosing_mail_sends_the_test_to_any_address_without_smtp(): void
    {
        Livewire::test(Plataforma::class)
            ->assertSet('smtpModo', 'php') // nothing saved yet: no server to fill in
            ->set('smtpRemetente', 'noreply@vencia.mundotutors.com')
            ->call('guardarSmtp')->assertHasNoErrors()
            ->set('testeDestino', 'qualquer.pessoa@exemplo.co.mz')
            ->call('enviarTeste')
            ->assertDispatched('toast', fn ($n, $p) => $p['title'] === 'Email de teste enviado');

        $this->assertTrue(Configuracao::temEnvio(Configuracao::plataforma('smtp')));
        $this->assertFalse(EmailsDeTeste::soNoRegisto(plataforma: true));

        $this->assertCount(1, self::$enviados);
        $e = self::$enviados[0];
        $this->assertSame('qualquer.pessoa@exemplo.co.mz', $e['para']);
        $this->assertStringContainsString('Email de teste', iconv_mime_decode($e['assunto'], 0, 'UTF-8'));
        $this->assertStringContainsString('From: Vencia <noreply@vencia.mundotutors.com>', $e['cabecalhos']);
        $this->assertStringNotContainsString("\r\nTo:", "\r\n".$e['cabecalhos'], 'To is mail()\'s own argument');
        $this->assertStringNotContainsString("\r\nSubject:", "\r\n".$e['cabecalhos']);
        $this->assertSame('-fnoreply@vencia.mundotutors.com', $e['parametros']);
        $this->assertStringContainsString('envio de emails está a funcionar', quoted_printable_decode($e['corpo']));
    }

    public function test_bcc_goes_in_the_headers(): void
    {
        Configuracao::guardarPlataforma('smtp', ['modo' => 'php', 'remetente' => 'noreply@vencia.test']);
        Configuracao::aplicarSmtpDaPlataforma();

        Mail::raw('Olá', fn ($m) => $m->to('a@exemplo.mz')->bcc('copia@exemplo.mz')->subject('Teste'));

        $this->assertStringContainsString('Bcc: copia@exemplo.mz', self::$enviados[0]['cabecalhos']);
    }

    public function test_a_refused_send_is_reported(): void
    {
        self::$falhar = true;
        Configuracao::guardarPlataforma('smtp', ['modo' => 'php']);
        Configuracao::aplicarSmtpDaPlataforma();

        $this->expectException(TransportException::class);
        Mail::raw('Olá', fn ($m) => $m->to('a@exemplo.mz')->subject('Teste'));
    }

    public function test_smtp_mode_still_needs_a_server(): void
    {
        Livewire::test(Plataforma::class)->set('smtpModo', 'smtp')->set('smtpServidor', '')
            ->call('guardarSmtp')->assertHasErrors(['smtpServidor']);
    }
}
