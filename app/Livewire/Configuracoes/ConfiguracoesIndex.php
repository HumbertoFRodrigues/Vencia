<?php

namespace App\Livewire\Configuracoes;

use App\Enums\IntervaloLembrete;
use App\Enums\MetodoPagamento;
use App\Enums\ServicoCategoria;
use App\Models\BibliotecaServico;
use App\Models\Configuracao;
use App\Models\MetodoPagamentoOpcao;
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
    public string $bibliotecaFiltro = 'activos';

    public ?int $editandoId = null;

    public string $novoNome = '';

    public string $novaCategoria = 'software';

    public ?string $novaDescricao = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novoLogo = null;

    // ---- Métodos de pagamento ----
    public ?int $editandoMetodoId = null;

    public string $novoMetodoNome = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novoMetodoLogo = null;

    // ---- Lembretes ----
    public ?string $editandoTemplate = null;

    public string $renovAssunto = '';

    public string $renovCorpo = '';

    public string $termAssunto = '';

    public string $termCorpo = '';

    // ---- Empresa ----
    public string $empresaNome = '';

    public string $empresaEmail = '';

    /**
     * Cosmetic only, per the admin's own confirmed choice — this changes what
     * currency code/timezone label the app shows, not any real conversion
     * math (Totais/MoneyValue keep computing plain MZN-shaped numbers; this
     * just relabels the display).
     */
    public string $empresaMoeda = 'MZN';

    public string $empresaFuso = 'Africa/Maputo';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $novoLogoEmpresa = null;

    public string $smtpServidor = '';

    public string $smtpPorta = '587';

    public string $smtpSeguranca = 'STARTTLS';

    public string $smtpUtilizador = '';

    /**
     * Deliberately never pre-filled from the saved value in mount() (unlike
     * every other SMTP field) — round-tripping an already-saved secret back
     * into the browser on every page load is worse practice than asking the
     * admin to re-type it when they want to change it. Left blank,
     * guardarSmtp() below keeps whatever password is already saved; typing
     * a new value replaces it.
     */
    public string $smtpPassword = '';

    public function mount(): void
    {
        $empresa = Configuracao::obter('empresa', []);
        $this->empresaNome = $empresa['nome'] ?? '';
        $this->empresaEmail = $empresa['email'] ?? '';
        $this->empresaMoeda = $empresa['moeda'] ?? 'MZN';
        $this->empresaFuso = $empresa['fuso_horario'] ?? 'Africa/Maputo';

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

    /** All items for the active filter (activos/arquivados) — separate sections, not one dimmed-together list. */
    #[Computed]
    public function itensBiblioteca(): Collection
    {
        return BibliotecaServico::query()
            ->where('arquivado', $this->bibliotecaFiltro === 'arquivados')
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function bibliotecaActivosCount(): int
    {
        return BibliotecaServico::query()->where('arquivado', false)->count();
    }

    #[Computed]
    public function bibliotecaArquivadosCount(): int
    {
        return BibliotecaServico::query()->where('arquivado', true)->count();
    }

    public function setBibliotecaFiltro(string $filtro): void
    {
        $this->bibliotecaFiltro = in_array($filtro, ['activos', 'arquivados'], true) ? $filtro : 'activos';
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
            fn (ServicoCategoria $c) => ['value' => $c->value, 'label' => $c->label()],
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
        unset($this->itensBiblioteca, $this->bibliotecaActivosCount, $this->bibliotecaArquivadosCount);

        $this->dispatch('toast', title: $mensagem, tone: 'success');
    }

    /** Toggles arquivado — never a hard delete, per the brief. */
    public function alternarArquivado(int $id): void
    {
        $item = BibliotecaServico::findOrFail($id);
        $item->update(['arquivado' => ! $item->arquivado]);

        unset($this->itensBiblioteca, $this->bibliotecaActivosCount, $this->bibliotecaArquivadosCount);

        $this->dispatch(
            'toast',
            title: $item->arquivado ? 'Serviço arquivado' : 'Serviço reactivado',
            body: $item->nome,
            tone: $item->arquivado ? 'info' : 'success',
        );
    }

    // ================= Métodos de pagamento =================

    /**
     * The 5 originally "well-known" método codes — the only ones with a real
     * hand-authored icon/logo in App\View\Components\Ui\PaymentMethod's
     * static map. Their MetodoPagamentoOpcao row's `nome` must stay exactly
     * this code (it's also the literal value stored on every existing
     * pagamentos.metodo / servicos.metodo_habitual row), so — unlike a
     * custom método — renaming is not offered for them here: only
     * arquivar/reactivar. A custom método's `nome` has no such constraint
     * and is freely editable, same as biblioteca_servicos' `nome`.
     *
     * @return list<string>
     */
    private function metodosConhecidos(): array
    {
        return array_column(MetodoPagamento::cases(), 'value');
    }

    /** Public: the blade view checks this per row to decide which row actions to offer. */
    public function metodoEhConhecido(string $nome): bool
    {
        return in_array($nome, $this->metodosConhecidos(), true);
    }

    /** All métodos, well-known first (insertion order), then any custom addition — mirrors itensBiblioteca's ordering intent. */
    #[Computed]
    public function itensMetodos(): Collection
    {
        return MetodoPagamentoOpcao::query()->orderBy('arquivado')->orderBy('id')->get();
    }

    #[Computed]
    public function editandoMetodoItem(): ?MetodoPagamentoOpcao
    {
        return $this->editandoMetodoId !== null ? MetodoPagamentoOpcao::find($this->editandoMetodoId) : null;
    }

    /** Drives the form: editing a custom método shows the rename+logo fields, editing a well-known one shows neither. */
    #[Computed]
    public function editandoMetodoEhConhecido(): bool
    {
        $item = $this->editandoMetodoItem;

        return $item !== null && $this->metodoEhConhecido($item->nome);
    }

    /** Only a custom (non-well-known) método can be renamed/given a custom logo — see metodosConhecidos()' docblock. */
    public function editarMetodo(int $id): void
    {
        $item = MetodoPagamentoOpcao::findOrFail($id);

        if ($this->metodoEhConhecido($item->nome)) {
            return;
        }

        $this->resetValidation();
        $this->editandoMetodoId = $id;
        $this->novoMetodoNome = $item->nome;
        $this->novoMetodoLogo = null;
    }

    public function cancelarEdicaoMetodo(): void
    {
        $this->resetValidation();
        $this->editandoMetodoId = null;
        $this->novoMetodoNome = '';
        $this->novoMetodoLogo = null;
    }

    public function guardarMetodo(): void
    {
        // A well-known método's row is edited (its nome must never change —
        // see metodosConhecidos()' docblock), never renamed through this form.
        if ($this->editandoMetodoId !== null && $this->metodoEhConhecido($this->editandoMetodoItem?->nome ?? '')) {
            $this->cancelarEdicaoMetodo();

            return;
        }

        $data = $this->validate([
            'novoMetodoNome' => ['required', 'string', 'max:255', Rule::unique('metodos_pagamento', 'nome')->ignore($this->editandoMetodoId)],
            'novoMetodoLogo' => ['nullable', 'image', 'max:2048'],
        ]);

        $payload = ['nome' => $data['novoMetodoNome']];

        if ($this->novoMetodoLogo) {
            $payload['logo_path'] = $this->novoMetodoLogo->store('logos', 'public');
        }

        if ($this->editandoMetodoId !== null) {
            MetodoPagamentoOpcao::findOrFail($this->editandoMetodoId)->update($payload);
            $mensagem = 'Método de pagamento actualizado';
        } else {
            $payload['arquivado'] = false;
            MetodoPagamentoOpcao::create($payload);
            $mensagem = 'Método de pagamento adicionado';
        }

        $this->cancelarEdicaoMetodo();
        unset($this->itensMetodos);

        $this->dispatch('toast', title: $mensagem, tone: 'success');
    }

    /** Toggles arquivado — never a hard delete, per the brief. Allowed for a well-known método too (only its nome is locked). */
    public function alternarArquivadoMetodo(int $id): void
    {
        $item = MetodoPagamentoOpcao::findOrFail($id);
        $item->update(['arquivado' => ! $item->arquivado]);

        unset($this->itensMetodos);

        $this->dispatch(
            'toast',
            title: $item->arquivado ? 'Método arquivado' : 'Método reactivado',
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
            'empresaMoeda' => ['required', 'string', Rule::in(['MZN', 'USD', 'ZAR'])],
            'empresaFuso' => ['required', 'string', Rule::in(['Africa/Maputo', 'UTC'])],
            'novoLogoEmpresa' => ['nullable', 'image', 'max:2048'],
        ]);

        $empresa = Configuracao::obter('empresa', []);
        $empresa['nome'] = $data['empresaNome'];
        $empresa['email'] = $data['empresaEmail'];
        $empresa['moeda'] = $data['empresaMoeda'];
        $empresa['fuso_horario'] = $data['empresaFuso'];

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
            'smtpPassword' => ['nullable', 'string', 'max:255'],
        ]);

        $existente = Configuracao::obter('smtp', []);

        Configuracao::query()->updateOrCreate(['chave' => 'smtp'], [
            'valor' => [
                'servidor' => $data['smtpServidor'],
                'porta' => $data['smtpPorta'],
                'seguranca' => $data['smtpSeguranca'],
                'utilizador' => $data['smtpUtilizador'],
                // Blank password field means "keep the one already saved" —
                // the field is never pre-filled (see its property docblock),
                // so an empty submit is not the admin asking to clear it.
                'password' => $data['smtpPassword'] !== '' ? $data['smtpPassword'] : ($existente['password'] ?? null),
            ],
        ]);

        $this->smtpPassword = '';
        $this->dispatch('toast', title: 'Configuração de SMTP guardada', tone: 'success');
    }

    /**
     * Real send attempt via whichever mailer is actually active for this
     * request: Configuracao::aplicarSmtpEmTempoDeExecucao() switches to the
     * admin's saved SMTP settings when one is configured, otherwise this
     * falls back to .env's MAIL_MAILER (today "log") unchanged — the same
     * runtime mailer VerificacaoDiariaService/SuspenderDialog now use, so
     * this button genuinely proves whether SMTP works, not just whether the
     * mail pipeline runs. Success or failure both surface as a toast; a
     * failure is also logged (never the password itself).
     */
    public function testarEmail(): void
    {
        $destino = trim($this->empresaEmail) !== '' ? $this->empresaEmail : Configuracao::obter('empresa', [])['email'] ?? null;

        if (! $destino) {
            $this->dispatch('toast', title: 'Sem email remetente configurado', body: 'Preencha o email remetente em Empresa primeiro.', tone: 'danger');

            return;
        }

        Configuracao::aplicarSmtpEmTempoDeExecucao();

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
