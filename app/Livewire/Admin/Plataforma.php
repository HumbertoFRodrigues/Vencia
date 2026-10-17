<?php

namespace App\Livewire\Admin;

use App\Http\Controllers\Auth\RegistoController;
use App\Models\ActividadeAdmin;
use App\Models\Configuracao;
use App\Services\CopiaSeguranca;
use App\Services\EmailsDeTeste;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
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
