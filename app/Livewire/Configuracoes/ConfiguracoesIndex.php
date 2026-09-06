<?php

namespace App\Livewire\Configuracoes;

use App\Enums\IntervaloLembrete;
use App\Enums\ServicoCategoria;
use App\Models\BibliotecaServico;
use App\Models\Configuracao;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Three independent tabs (Biblioteca de Serviços / Lembretes / Empresa),
 * each with its own cleanly separated save logic — kept in one component
 * (not three) because they share nothing but the tab-state chrome and
 * splitting them into three routes would just add navigation for no
 * benefit, per the brief's "your call" on composition.
 *
 * Every field here that the mail engine actually reads is stored under the
 * exact Configuracao keys App\Mail\LembreteVencimentoMail and
 * App\Mail\ServicoTerminadoMail already read live
 * (email_template_renovacao / email_template_servico_terminado / empresa /
 * lembretes) — see those Mailables' class docblocks. Editing a template here
 * takes effect on the next send with zero Mailable changes.
 */
#[Layout('layouts.app')]
#[Title('Configurações')]
class ConfiguracoesIndex extends Component
{
    use WithFileUploads;

    /** @var array<string, string> */
    private const CATEGORIA_LABELS = [
        'dominio' => 'Domínio',
        'hospedagem' => 'Hospedagem',
        'email' => 'Email',
        'ia' => 'IA',
        'software' => 'Software',
        'manutencao' => 'Manutenção',
        'desenvolvimento' => 'Desenvolvimento',
        'outro' => 'Outro',
    ];

    /** @var array<string, string> */
    private const INTERVALO_LABELS = [
        'd30' => '30 dias antes',
        'd15' => '15 dias antes',
        'd7' => '7 dias antes',
        'd3' => '3 dias antes',
        'd1' => '1 dia antes',
        'no_dia' => 'No dia do vencimento',
        'apos_vencimento' => 'Depois do vencimento',
    ];

    #[Url]
    public string $tab = 'biblioteca';

    // ---- Biblioteca de Serviços ----
    public ?int $editandoId = null;

    public string $novoNome = '';

    public string $novaCategoria = 'software';

    public ?string $novaDescricao = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novoLogo = null;

    // ---- Lembretes ----
    public ?string $editandoTemplate = null;

    public string $renovAssunto = '';

    public string $renovCorpo = '';

    public string $termAssunto = '';

    public string $termCorpo = '';

    // ---- Empresa ----
    public string $empresaNome = '';

    public string $empresaEmail = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novoLogoEmpresa = null;

    public string $smtpServidor = '';

    public string $smtpPorta = '587';

    public string $smtpSeguranca = 'STARTTLS';

    public string $smtpUtilizador = '';

    public function mount(): void
    {
        $empresa = Configuracao::obter('empresa', []);
        $this->empresaNome = $empresa['nome'] ?? '';
        $this->empresaEmail = $empresa['email'] ?? '';

        $smtp = Configuracao::obter('smtp', []);
        $this->smtpServidor = $smtp['servidor'] ?? '';
        $this->smtpPorta = $smtp['porta'] ?? '587';
        $this->smtpSeguranca = $smtp['seguranca'] ?? 'STARTTLS';
        $this->smtpUtilizador = $smtp['utilizador'] ?? '';

        $this->recarregarTemplates();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    // ================= Biblioteca de Serviços =================

    /** @return Collection<int, BibliotecaServico> */
    #[Computed]
    public function itensBiblioteca(): Collection
    {
        return BibliotecaServico::query()->orderBy('arquivado')->orderBy('nome')->get();
    }

    #[Computed]
    public function editandoItem(): ?BibliotecaServico
    {
        return $this->editandoId !== null ? BibliotecaServico::find($this->editandoId) : null;
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function categoriaOptions(): array
    {
        return array_map(
            fn (ServicoCategoria $c) => ['value' => $c->value, 'label' => self::CATEGORIA_LABELS[$c->value]],
            ServicoCategoria::cases(),
        );
    }

    public function editarBiblioteca(int $id): void
    {
        $item = BibliotecaServico::findOrFail($id);

        $this->resetValidation();
        $this->editandoId = $id;
        $this->novoNome = $item->nome;
        $this->novaCategoria = $item->categoria->value;
        $this->novaDescricao = $item->descricao_padrao;
        $this->novoLogo = null;
    }

    public function cancelarEdicaoBiblioteca(): void
    {
        $this->resetValidation();
        $this->editandoId = null;
        $this->novoNome = '';
        $this->novaCategoria = ServicoCategoria::Software->value;
        $this->novaDescricao = null;
        $this->novoLogo = null;
    }

    public function guardarBiblioteca(): void
    {
        $data = $this->validate([
            'novoNome' => ['required', 'string', 'max:255', Rule::unique('biblioteca_servicos', 'nome')->ignore($this->editandoId)],
            'novaCategoria' => ['required', Rule::enum(ServicoCategoria::class)],
            'novaDescricao' => ['nullable', 'string', 'max:500'],
            'novoLogo' => ['nullable', 'image', 'max:2048'],
        ]);

        $payload = [
            'nome' => $data['novoNome'],
            'categoria' => $data['novaCategoria'],
            'descricao_padrao' => $data['novaDescricao'] !== null && trim($data['novaDescricao']) !== '' ? $data['novaDescricao'] : null,
        ];

        if ($this->novoLogo) {
            $payload['logo_path'] = $this->novoLogo->store('logos', 'public');
        }

        if ($this->editandoId !== null) {
            BibliotecaServico::findOrFail($this->editandoId)->update($payload);
            $mensagem = 'Serviço da biblioteca actualizado';
        } else {
            $payload['arquivado'] = false;
            BibliotecaServico::create($payload);
            $mensagem = 'Serviço adicionado à biblioteca';
        }

        $this->cancelarEdicaoBiblioteca();
        unset($this->itensBiblioteca);

        $this->dispatch('toast', title: $mensagem, tone: 'success');
    }

    /** Toggles arquivado — never a hard delete, per the brief. */
    public function alternarArquivado(int $id): void
    {
        $item = BibliotecaServico::findOrFail($id);
        $item->update(['arquivado' => ! $item->arquivado]);

        unset($this->itensBiblioteca);

        $this->dispatch(
            'toast',
            title: $item->arquivado ? 'Serviço arquivado' : 'Serviço reactivado',
            body: $item->nome,
            tone: $item->arquivado ? 'info' : 'success',
        );
    }

    // ================= Lembretes =================

    #[Computed]
    public function lembretesGlobais(): array
    {
        return Configuracao::obter('lembretes', []);
    }

    /** @return array<string, string> */
    #[Computed]
    public function intervaloLabels(): array
    {
        return self::INTERVALO_LABELS;
    }

    public function alternarVerificacaoDiaria(): void
    {
        $this->atualizarLembretes('verificacao_diaria', ! (bool) ($this->lembretesGlobais['verificacao_diaria'] ?? false));
    }

    public function alternarIntervaloGlobal(string $intervalo): void
    {
        if (! array_key_exists($intervalo, self::INTERVALO_LABELS)) {
            return;
        }

        $this->atualizarLembretes($intervalo, ! (bool) ($this->lembretesGlobais[$intervalo] ?? false));
    }

    private function atualizarLembretes(string $chave, bool $valor): void
    {
        $lembretes = Configuracao::obter('lembretes', []);
        $lembretes[$chave] = $valor;

        Configuracao::query()->updateOrCreate(['chave' => 'lembretes'], ['valor' => $lembretes]);

        unset($this->lembretesGlobais);
    }

    public function editarTemplate(string $chave): void
    {
        $this->editandoTemplate = $chave;
    }

    public function cancelarTemplate(): void
    {
        $this->editandoTemplate = null;
        $this->recarregarTemplates();
    }

    private function recarregarTemplates(): void
    {
        $renovacao = Configuracao::obter('email_template_renovacao', []);
        $this->renovAssunto = $renovacao['assunto'] ?? '';
        $this->renovCorpo = $renovacao['corpo'] ?? '';

        $terminado = Configuracao::obter('email_template_servico_terminado', []);
        $this->termAssunto = $terminado['assunto'] ?? '';
        $this->termCorpo = $terminado['corpo'] ?? '';
    }

    public function guardarTemplateRenovacao(): void
    {
        $data = $this->validate([
            'renovAssunto' => ['required', 'string', 'max:255'],
            'renovCorpo' => ['required', 'string'],
        ]);

        Configuracao::query()->updateOrCreate(['chave' => 'email_template_renovacao'], [
            'valor' => ['assunto' => $data['renovAssunto'], 'corpo' => $data['renovCorpo']],
        ]);

        $this->editandoTemplate = null;
        $this->dispatch('toast', title: 'Template de renovação actualizado', tone: 'success');
    }

    public function guardarTemplateTerminado(): void
    {
        $data = $this->validate([
            'termAssunto' => ['required', 'string', 'max:255'],
            'termCorpo' => ['required', 'string'],
        ]);

        Configuracao::query()->updateOrCreate(['chave' => 'email_template_servico_terminado'], [
            'valor' => ['assunto' => $data['termAssunto'], 'corpo' => $data['termCorpo']],
        ]);

        $this->editandoTemplate = null;
        $this->dispatch('toast', title: 'Template de serviço terminado actualizado', tone: 'success');
    }

    /** Highlights the [TOKEN] placeholders for the read-only template preview, same tokens the Mailables replace. */
    public function destacarPlaceholders(string $texto): string
    {
        $escaped = e($texto);
        $destacado = preg_replace(
            '/\[(NOME|SERVIÇO|VALOR|DATA|NOME DA EMPRESA)\]/u',
            '<mark style="background:var(--accent-soft);color:var(--text-accent);padding:0 3px;border-radius:3px">[$1]</mark>',
            $escaped,
        );

        return nl2br($destacado ?? $escaped);
    }

    // ================= Empresa =================

    public function guardarEmpresa(): void
    {
        $data = $this->validate([
            'empresaNome' => ['required', 'string', 'max:255'],
            'empresaEmail' => ['required', 'email', 'max:255'],
            'novoLogoEmpresa' => ['nullable', 'image', 'max:2048'],
        ]);

        $empresa = Configuracao::obter('empresa', []);
        $empresa['nome'] = $data['empresaNome'];
        $empresa['email'] = $data['empresaEmail'];
        $empresa['moeda'] = $empresa['moeda'] ?? 'MZN';
        $empresa['fuso_horario'] = $empresa['fuso_horario'] ?? 'Africa/Maputo';

        if ($this->novoLogoEmpresa) {
            $empresa['logo_path'] = $this->novoLogoEmpresa->store('logos', 'public');
        }

        Configuracao::query()->updateOrCreate(['chave' => 'empresa'], ['valor' => $empresa]);

        $this->novoLogoEmpresa = null;
        $this->dispatch('toast', title: 'Dados da empresa actualizados', tone: 'success');
    }

    public function guardarSmtp(): void
    {
        $data = $this->validate([
            'smtpServidor' => ['nullable', 'string', 'max:255'],
            'smtpPorta' => ['nullable', 'string', 'max:10'],
            'smtpSeguranca' => ['required', 'string'],
            'smtpUtilizador' => ['nullable', 'string', 'max:255'],
        ]);

        Configuracao::query()->updateOrCreate(['chave' => 'smtp'], [
            'valor' => [
                'servidor' => $data['smtpServidor'],
                'porta' => $data['smtpPorta'],
                'seguranca' => $data['smtpSeguranca'],
                'utilizador' => $data['smtpUtilizador'],
            ],
        ]);

        $this->dispatch('toast', title: 'Configuração de SMTP guardada', tone: 'success');
    }

    /**
     * Real send attempt via the app's current mailer (MAIL_MAILER=log until
     * real SMTP credentials exist in .env, per the brief) — proves the
     * mechanism end-to-end without needing working SMTP yet. Success or
     * failure both surface as a toast; a failure is also logged.
     */
    public function testarEmail(): void
    {
        $destino = trim($this->empresaEmail) !== '' ? $this->empresaEmail : Configuracao::obter('empresa', [])['email'] ?? null;

        if (! $destino) {
            $this->dispatch('toast', title: 'Sem email remetente configurado', body: 'Preencha o email remetente em Empresa primeiro.', tone: 'danger');

            return;
        }

        try {
            Mail::raw('Isto é um email de teste do Vencia.', function ($message) use ($destino): void {
                $message->to($destino)->subject('Email de teste — Vencia');
            });

            $this->dispatch('toast', title: 'Email de teste enviado', body: $destino, tone: 'success');
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar email de teste do Vencia: '.$e->getMessage());

            $this->dispatch('toast', title: 'Falha ao enviar email de teste', body: 'Verifique a configuração SMTP.', tone: 'danger');
        }
    }

    public function render()
    {
        return view('livewire.configuracoes.configuracoes-index');
    }
}
