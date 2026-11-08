<?php

namespace Tests\Feature\Contas;

use App\Livewire\Admin\Plataforma;
use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

/** Sending through Brevo's API (Admin → Plataforma → Envio de email), against a faked Brevo. */
class EnvioBrevoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['super_admin' => true]));
    }

    public function test_the_key_is_stored_encrypted_and_the_test_goes_through_brevo(): void
    {
        Http::fake(['api.brevo.com/v3/smtp/email' => Http::response(['messageId' => '<abc@brevo>'], 201)]);

        Livewire::test(Plataforma::class)
            ->set('smtpModo', 'brevo')
            ->set('brevoChave', 'xkeysib-chave-secreta')
            ->set('smtpRemetente', 'no_replay@mundotutors.com')
            ->call('guardarSmtp')->assertHasNoErrors()
            ->set('testeDestino', 'alguem@outlook.com')
            ->call('enviarTeste')
            ->assertDispatched('toast', fn ($n, $p) => $p['title'] === 'Email de teste enviado');

        $this->assertStringNotContainsString('xkeysib-chave-secreta', json_encode(Configuracao::plataforma('smtp')));

        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.brevo.com/v3/smtp/email'
            && $r->header('api-key')[0] === 'xkeysib-chave-secreta'
            && $r['sender']['email'] === 'no_replay@mundotutors.com'
            && $r['sender']['name'] === 'Vencia'
            && $r['to'][0]['email'] === 'alguem@outlook.com'
            && str_contains($r['textContent'], 'envio de emails está a funcionar'));
    }

    public function test_business_emails_carry_the_business_name_and_reply_to(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => 'x'], 201)]);
        Configuracao::guardarPlataforma('smtp', ['modo' => 'brevo', 'brevo_chave' => encrypt('k', false), 'remetente' => 'no_replay@mundotutors.com']);
        Configuracao::query()->updateOrCreate(['chave' => 'empresa'], ['valor' => ['nome' => 'Ginásio Sol', 'email' => 'geral@ginasiosol.co.mz']]);

        Livewire::test(ConfiguracoesIndex::class, ['tab' => 'empresa'])
            ->set('testeDestino', 'geral@ginasiosol.co.mz')
            ->call('enviarTeste')->assertHasNoErrors();

        Http::assertSent(fn (Request $r) => $r['sender'] === ['email' => 'no_replay@mundotutors.com', 'name' => 'Ginásio Sol']
            && $r['replyTo']['email'] === 'geral@ginasiosol.co.mz');
    }

    public function test_a_wrong_key_is_explained(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['code' => 'unauthorized', 'message' => 'Key not found'], 401)]);
        Configuracao::guardarPlataforma('smtp', ['modo' => 'brevo', 'brevo_chave' => encrypt('errada', false), 'remetente' => 'a@b.mz']);
        Configuracao::aplicarSmtpDaPlataforma();

        try {
            Mail::raw('Olá', fn ($m) => $m->to('x@y.mz')->subject('Teste'));
            $this->fail('should have thrown');
        } catch (TransportException $e) {
            $this->assertStringContainsString('chave API do Brevo não é válida', $e->getMessage());
        }
    }

    public function test_brevo_mode_needs_a_key_and_a_sender(): void
    {
        Livewire::test(Plataforma::class)->set('smtpModo', 'brevo')->set('smtpRemetente', '')
            ->call('guardarSmtp')->assertHasErrors(['brevoChave', 'smtpRemetente']);
    }

    public function test_the_company_settings_have_no_mail_server_any_more(): void
    {
        Livewire::test(ConfiguracoesIndex::class, ['tab' => 'empresa'])
            ->assertSee('Emails aos teus clientes')
            ->assertDontSee('Servidor de email')
            ->assertDontSee('STARTTLS');
    }
}
