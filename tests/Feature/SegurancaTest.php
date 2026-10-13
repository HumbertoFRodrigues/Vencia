<?php

namespace Tests\Feature;

use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Livewire\Servicos\RenovarDialog;
use App\Models\Configuracao;
use App\Models\User;
use App\Services\RelatorioPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class SegurancaTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_carry_security_headers(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_forgot_password_does_not_reveal_whether_an_account_exists(): void
    {
        User::factory()->create(['email' => 'existe@teste.mz']);

        $existe = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'existe@teste.mz']);
        $naoExiste = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nao@teste.mz']);

        $existe->assertSessionHas('status');
        $naoExiste->assertSessionHas('status', $existe->getSession()->get('status'));
        $naoExiste->assertSessionHasNoErrors();
    }

    public function test_smtp_password_is_stored_encrypted(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ConfiguracoesIndex::class)
            ->set('smtpServidor', 'smtp.teste.mz')
            ->set('smtpPorta', '587')
            ->set('smtpSeguranca', 'STARTTLS')
            ->set('smtpPassword', 'segredo-123')
            ->call('guardarSmtp')
            ->assertHasNoErrors();

        $guardada = Configuracao::obter('smtp')['password'];
        $this->assertNotSame('segredo-123', $guardada);
        $this->assertSame('segredo-123', Crypt::decryptString($guardada));

        Configuracao::aplicarSmtpEmTempoDeExecucao();
        $this->assertSame('segredo-123', config('mail.mailers.smtp.password'));
    }

    public function test_dialog_ids_cannot_be_changed_from_the_browser(): void
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(RenovarDialog::class)->set('servicoId', 1);
    }

    public function test_pdf_reports_render_without_php_in_templates(): void
    {
        $resposta = RelatorioPdf::gerar('Teste', 'Sub', ['A', 'B'], [['x', '1']], 'teste.pdf');

        ob_start();
        $resposta->sendContent();
        $pdf = ob_get_clean();

        $this->assertStringStartsWith('%PDF', $pdf);
    }
}
