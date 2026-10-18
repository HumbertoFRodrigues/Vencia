<?php

namespace App\Livewire\Admin;

use App\Http\Controllers\Auth\RegistoController;
use App\Models\ActividadeAdmin;
use App\Models\Configuracao;
use App\Services\CopiaSeguranca;
use App\Services\EmailsDeTeste;
use App\Services\Pagamentos\MpesaCliente;
use App\Services\Pagamentos\MpesaConfig;
use App\Support\Anuncios;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * The platform's own settings: the SMTP every account email (and every
 * business without its own server) goes through, the payment details shown
 * on plan invoices, sign-up options, test emails and database backups.
 */
#[Layout('layouts.app')]
#[Title('Plataforma')]
class Plataforma extends Component
{
    public string $smtpServidor = '';

    public string $smtpPorta = '587';

    public string $smtpSeguranca = 'STARTTLS';

    public string $smtpUtilizador = '';

    /** Never pre-filled: blank on save keeps the stored one. */
    public string $smtpPassword = '';

    public string $smtpRemetente = '';

    public string $moeda = 'MZN';

    public string $mpesa = '';

    public string $emola = '';

    public string $banco = '';

    public string $titular = '';

    public string $nif = '';

    public string $emailFaturacao = '';

    public bool $registoAberto = true;

    public string $diasTeste = '14';

    public bool $avisarAdmin = true;

    public string $testeTipo = 'simples';

    public string $testeDestino = '';

    public function mount(): void
    {
        $smtp = Configuracao::plataforma('smtp', []);
        $this->smtpServidor = $smtp['servidor'] ?? '';
        $this->smtpPorta = (string) ($smtp['porta'] ?? '587');
        $this->smtpSeguranca = $smtp['seguranca'] ?? 'STARTTLS';
        $this->smtpUtilizador = $smtp['utilizador'] ?? '';
        $this->smtpRemetente = $smtp['remetente'] ?? '';

        $f = Configuracao::plataforma('faturacao', []);
        $this->moeda = $f['moeda'] ?? 'MZN';
        $this->mpesa = $f['mpesa'] ?? '';
        $this->emola = $f['emola'] ?? '';
        $this->banco = $f['banco'] ?? '';
        $this->titular = $f['titular'] ?? '';
        $this->nif = $f['nif'] ?? '';
        $this->emailFaturacao = $f['email'] ?? '';

        $registo = RegistoController::opcoes();
        $this->registoAberto = $registo['aberto'];
        $this->diasTeste = (string) $registo['dias_teste'];
        $this->avisarAdmin = (bool) (Configuracao::plataforma('registo', [])['avisar_admin'] ?? true);

        $mpesa = MpesaConfig::get();
        $this->mpesaLigado = $mpesa['ligado'];
        $this->mpesaAmbiente = $mpesa['sandbox'] ? 'sandbox' : 'producao';
        $this->mpesaCodigo = (string) ($mpesa['service_provider_code'] ?? '');

        $anuncios = Anuncios::get();
        $this->anunciosLigado = $anuncios['ligado'];
        $this->anunciosCliente = $anuncios['cliente'];

        $this->testeDestino = auth()->user()->email;
    }

    public function guardarSmtp(): void
    {
        $dados = $this->validate([
            'smtpServidor' => ['nullable', 'string', 'max:255'],
            'smtpPorta' => ['nullable', 'integer', 'between:1,65535'],
            'smtpSeguranca' => ['required', Rule::in(['STARTTLS', 'SSL', 'Nenhuma'])],
            'smtpUtilizador' => ['nullable', 'string', 'max:255'],
            'smtpPassword' => ['nullable', 'string', 'max:255'],
            'smtpRemetente' => ['nullable', 'email', 'max:255'],
        ], [], ['smtpRemetente' => 'remetente', 'smtpPorta' => 'porta']);

        $existente = Configuracao::plataforma('smtp', []);

        Configuracao::guardarPlataforma('smtp', [
            'servidor' => $dados['smtpServidor'],
            'porta' => (string) $dados['smtpPorta'],
            'seguranca' => $dados['smtpSeguranca'],
            'utilizador' => $dados['smtpUtilizador'],
            'remetente' => $dados['smtpRemetente'],
            'password' => $dados['smtpPassword'] !== '' ? Crypt::encryptString($dados['smtpPassword']) : ($existente['password'] ?? null),
        ]);

        $this->smtpPassword = '';
        ActividadeAdmin::registar('plataforma.smtp', null, $dados['smtpServidor']);
        $this->dispatch('toast', title: 'SMTP da plataforma guardado', tone: 'success');
    }

    public function guardarFaturacao(): void
    {
        $dados = $this->validate([
            'moeda' => ['required', 'string', 'max:5'],
            'mpesa' => ['nullable', 'string', 'max:60'],
            'emola' => ['nullable', 'string', 'max:60'],
            'banco' => ['nullable', 'string', 'max:300'],
            'titular' => ['nullable', 'string', 'max:120'],
            'nif' => ['nullable', 'string', 'max:30'],
            'emailFaturacao' => ['nullable', 'email', 'max:255'],
        ], [], ['emailFaturacao' => 'email']);

        Configuracao::guardarPlataforma('faturacao', [
            'moeda' => strtoupper($dados['moeda']),
            'mpesa' => $dados['mpesa'], 'emola' => $dados['emola'], 'banco' => $dados['banco'],
            'titular' => $dados['titular'], 'nif' => $dados['nif'], 'email' => $dados['emailFaturacao'],
            'nome' => config('app.name'),
        ]);

        ActividadeAdmin::registar('plataforma.faturacao');
        $this->dispatch('toast', title: 'Dados de pagamento guardados', tone: 'success');
    }

    public bool $mpesaLigado = false;

    public string $mpesaAmbiente = 'sandbox';

    /** Never pre-filled: blank on save keeps the stored key. */
    public string $mpesaApiKey = '';

    public string $mpesaPublicKey = '';

    public string $mpesaCodigo = '';

    public function guardarMpesa(): void
    {
        $this->validate([
            'mpesaApiKey' => ['nullable', 'string', 'max:200'],
            'mpesaPublicKey' => ['nullable', 'string', 'max:2000'],
            'mpesaCodigo' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'mpesaAmbiente' => ['required', 'in:sandbox,producao'],
        ], ['mpesaCodigo.regex' => 'O Service Provider Code só tem números (ex.: 171717).'], ['mpesaCodigo' => 'Service Provider Code']);

        if ($this->mpesaLigado && ! filled($this->mpesaApiKey) && ! filled(Configuracao::plataforma('mpesa', [])['api_key'] ?? null)) {
            $this->addError('mpesaApiKey', 'Cola a API Key antes de ligar.');

            return;
        }

        MpesaConfig::guardar([
            'ligado' => $this->mpesaLigado,
            'sandbox' => $this->mpesaAmbiente !== 'producao',
            'api_key' => $this->mpesaApiKey,
            'public_key' => $this->mpesaPublicKey,
            'service_provider_code' => $this->mpesaCodigo,
        ]);

        $this->reset('mpesaApiKey', 'mpesaPublicKey');
        $this->mpesaCodigo = (string) (MpesaConfig::get()['service_provider_code'] ?? '');
        ActividadeAdmin::registar('plataforma.mpesa', null, ($this->mpesaLigado ? 'ligado' : 'desligado').($this->mpesaAmbiente === 'producao' ? ' (produção)' : ' (sandbox)'));

        if ($this->mpesaLigado && ! MpesaConfig::completo()) {
            $this->dispatch('toast', title: 'Guardado, mas ainda desligado', body: 'Faltam chaves: API Key, Public Key e Service Provider Code.', tone: 'warning');

            return;
        }

        $this->dispatch('toast', title: 'M-Pesa guardado', body: MpesaConfig::activo() ? 'Os clientes já vêem "Pagar já com M-Pesa".' : 'Desligado: os clientes pagam à mão.', tone: 'success');
    }

    /** Checks the saved keys fit together (the API Key encrypts with the Public Key). No money moves. */
    public function verificarMpesa(): void
    {
        $config = MpesaConfig::get();

        if (! MpesaConfig::completo()) {
            $this->dispatch('toast', title: 'Faltam chaves', body: 'Guarda a API Key, a Public Key e o Service Provider Code primeiro.', tone: 'warning');

            return;
        }

        try {
            app(MpesaCliente::class)->bearer($config['api_key'], $config['public_key']);
        } catch (RuntimeException $e) {
            $this->dispatch('toast', title: 'Chaves inválidas', body: $e->getMessage(), tone: 'danger');

            return;
        }

        $this->dispatch('toast', title: 'Chaves válidas', body: 'Para confirmar com a Vodacom, faz um pagamento de teste'.($config['sandbox'] ? ' com o número de teste do portal.' : ' de um plano.'), tone: 'success');
    }

    public bool $anunciosLigado = false;

    /** AdSense publisher ID; the whole <script> Google shows can be pasted, only the ID is kept. */
    public string $anunciosCliente = '';

    public function guardarAnuncios(): void
    {
        $this->anunciosCliente = Anuncios::extrairCliente($this->anunciosCliente);

        $this->validate([
            'anunciosCliente' => [$this->anunciosLigado ? 'required' : 'nullable', 'regex:'.Anuncios::FORMATO],
        ], [
            'anunciosCliente.regex' => 'O ID de editor tem o formato ca-pub-1234567890123456.',
        ], ['anunciosCliente' => 'ID de editor']);

        Configuracao::guardarPlataforma('anuncios', ['ligado' => $this->anunciosLigado, 'cliente' => $this->anunciosCliente]);
        ActividadeAdmin::registar('plataforma.anuncios', null, ($this->anunciosLigado ? 'ligados' : 'desligados').($this->anunciosCliente ? ', '.$this->anunciosCliente : ''));
        $this->dispatch('toast', title: $this->anunciosLigado ? 'Anúncios ligados' : 'Anúncios desligados', tone: 'success');
    }

    public function guardarRegisto(): void
    {
        $this->validate(['diasTeste' => ['required', 'integer', 'min:0', 'max:90']], [], ['diasTeste' => 'dias de teste']);

        Configuracao::guardarPlataforma('registo', ['aberto' => $this->registoAberto, 'dias_teste' => (int) $this->diasTeste, 'avisar_admin' => $this->avisarAdmin]);
        ActividadeAdmin::registar('plataforma.registo', null, ($this->registoAberto ? 'aberto' : 'fechado').', '.$this->diasTeste.' dias de teste');
        $this->dispatch('toast', title: 'Registo actualizado', tone: 'success');
    }

    public function enviarTeste(): void
    {
        $this->validate([
            'testeTipo' => ['required', Rule::in(EmailsDeTeste::TIPOS_PLATAFORMA)],
            'testeDestino' => ['required', 'email'],
        ], [], ['testeTipo' => 'tipo de email', 'testeDestino' => 'email de destino']);

        try {
            app(EmailsDeTeste::class)->enviar($this->testeTipo, $this->testeDestino, plataforma: true);
        } catch (Throwable $e) {
            Log::error('Falha ao enviar email de teste da plataforma ('.$this->testeTipo.'): '.$e->getMessage());
            $this->dispatch('toast', title: 'Falha ao enviar', body: mb_strimwidth($e->getMessage(), 0, 160, '…'), tone: 'danger');

            return;
        }

        if (EmailsDeTeste::soNoRegisto(plataforma: true)) {
            $this->dispatch('toast', title: 'SMTP por configurar', body: 'O email foi gravado só no registo (storage/logs), não saiu para '.$this->testeDestino.'.', tone: 'info');

            return;
        }

        unset($this->copias);
        $this->dispatch('toast', title: 'Email de teste enviado', body: EmailsDeTeste::TIPOS[$this->testeTipo].' → '.$this->testeDestino, tone: 'success');
    }

    #[Computed]
    public function copias(): array
    {
        return app(CopiaSeguranca::class)->existentes();
    }

    public function criarCopia(): BinaryFileResponse
    {
        $ficheiro = app(CopiaSeguranca::class)->criar();
        unset($this->copias);
        ActividadeAdmin::registar('plataforma.copia', null, basename($ficheiro));
        $this->dispatch('toast', title: 'Cópia criada', body: basename($ficheiro), tone: 'success');

        return response()->download($ficheiro);
    }

    public function descarregarCopia(string $nome): ?BinaryFileResponse
    {
        // Only a file name from our own listing — never a path.
        if (! preg_match('/^vencia-\d{4}-\d{2}-\d{2}_\d{6}\.sql\.gz$/', $nome)) {
            return null;
        }

        $ficheiro = app(CopiaSeguranca::class)->pasta().'/'.$nome;

        return is_file($ficheiro) ? response()->download($ficheiro) : null;
    }

    public function render()
    {
        return view('livewire.admin.plataforma');
    }
}
