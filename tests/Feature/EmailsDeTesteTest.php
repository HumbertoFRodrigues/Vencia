<?php

namespace Tests\Feature;

use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\User;
use App\Services\CopiaSeguranca;
use App\Services\EmailsDeTeste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class EmailsDeTesteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Configuracao::create(['chave' => 'empresa', 'valor' => ['nome' => 'Empresa X', 'email' => 'empresa@teste.mz']]);
        $this->app->instance(CopiaSeguranca::class, new class extends CopiaSeguranca
        {
            public function pasta(): string
            {
                return storage_path('framework/testing/copias');
            }
        });
        File::deleteDirectory(storage_path('framework/testing/copias'));
    }

    private function enviados(): array
    {
        return app('mailer')->getSymfonyTransport()->messages()->all();
    }

    public function test_every_type_reaches_the_chosen_address_without_saving_anything(): void
    {
        $esperado = [
            'simples' => 'Email de teste',
            'lembrete' => 'vencimento',
            'terminado' => 'suspenso',
            'recibo' => 'Recibo #000000',
            'password' => 'Reposição da password',
            'copia' => 'Cópia de segurança',
            'erro' => 'Erro na Vencia',
        ];
        $this->assertSame(array_keys(EmailsDeTeste::TIPOS), array_keys($esperado));

        foreach ($esperado as $tipo => $assunto) {
            Livewire::test(ConfiguracoesIndex::class, ['tab' => 'empresa'])
                ->set('testeTipo', $tipo)->set('testeDestino', 'eu@teste.mz')
                ->call('enviarTeste')->assertHasNoErrors()
                ->assertDispatched('toast', fn ($nome, $p) => $p['tone'] === 'success');
        }

        $mensagens = $this->enviados();
        $this->assertCount(count($esperado), $mensagens);

        foreach (array_values($esperado) as $i => $assunto) {
            $email = $mensagens[$i]->getOriginalMessage();
            $this->assertSame(['eu@teste.mz'], array_map(fn ($a) => $a->getAddress(), $email->getTo()));
            $this->assertStringContainsStringIgnoringCase($assunto, $email->getSubject());
        }

        $anexos = $mensagens[3]->getOriginalMessage()->getAttachments();
        $this->assertSame('application/pdf', $anexos[0]->getMediaType().'/'.$anexos[0]->getMediaSubtype());

        $this->assertSame(0, Cliente::count());
        $this->assertSame(0, Historico::count());
    }

    public function test_defaults_to_the_empresa_email_and_validates_the_address(): void
    {
        Livewire::test(ConfiguracoesIndex::class, ['tab' => 'empresa'])
            ->assertSet('testeDestino', 'empresa@teste.mz')
            ->set('testeDestino', 'nao-e-email')->call('enviarTeste')->assertHasErrors('testeDestino')
            ->set('testeDestino', 'ok@teste.mz')->set('testeTipo', 'inventado')->call('enviarTeste')->assertHasErrors('testeTipo');

        $this->assertCount(0, $this->enviados());
    }

    public function test_warns_when_mail_only_goes_to_the_log(): void
    {
        config(['mail.default' => 'log']);

        Livewire::test(ConfiguracoesIndex::class, ['tab' => 'empresa'])
            ->set('testeTipo', 'simples')->call('enviarTeste')
            ->assertDispatched('toast', fn ($nome, $p) => $p['title'] === 'SMTP por configurar');
    }
}
