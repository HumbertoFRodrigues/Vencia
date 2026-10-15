<?php

namespace App\Livewire\Configuracoes;

use App\Enums\MetodoPagamento;
use App\Enums\ServicoCategoria;
use App\Models\BibliotecaServico;
use App\Models\Configuracao;
use App\Models\MetodoPagamentoOpcao;
use App\Services\EmailsDeTeste;
use App\Services\ServicoStatusService;
use App\Support\ContaActual;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

    /** @var TemporaryUploadedFile|null */
    public $novoLogo = null;

    // ---- Métodos de pagamento ----
    public ?int $editandoMetodoId = null;

    public string $novoMetodoNome = '';

    /** @var TemporaryUploadedFile|null */
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

    /** @var TemporaryUploadedFile|null */
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

    /** "Testar emails" card: which sample to send and where. */
    public string $testeTipo = 'simples';

    public string $testeDestino = '';

    /** Days before the due date the reminders start, per billing cycle (see ServicoStatusService::ANTECEDENCIA_PADRAO). */
    public array $antecedencias = [];

    public function mount(): void
    {
        $this->setTab($this->tab);

        $this->antecedencias = array_merge(
            ServicoStatusService::ANTECEDENCIA_PADRAO,
            array_intersect_key(Configuracao::obter('lembretes', [])['antecedencia'] ?? [], ServicoStatusService::ANTECEDENCIA_PADRAO),
        );
        $empresa = Configuracao::obter('empresa', []);
        $this->empresaNome = $empresa['nome'] ?? '';
        $this->empresaEmail = $empresa['email'] ?? '';
        $this->testeDestino = $this->empresaEmail;
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
        $this->tab = in_array($tab, ['biblioteca', 'lembretes', 'empresa', 'seguranca'], true) ? $tab : 'biblioteca';
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
            'novoNome' => ['required', 'string', 'max:255', Rule::unique('biblioteca_servicos', 'nome')->ignore($this->editandoId)->where('conta_id', ContaActual::id())],
            'novaCategoria' => ['required', Rule::enum(ServicoCategoria::class)],
            'novaDescricao' => ['nullable', 'string', 'max:500'],
            'novoLogo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
        ]);

        $payload = [
            'nome' => $data['novoNome'],
            'categoria' => $data['novaCategoria'],
            'descricao_padrao' => $data['novaDescricao'] !== null && trim($data['novaDescricao']) !== '' ? $data['novaDescricao'] : null,
        ];

        if ($this->novoLogo) {
            $payload['logo_path'] = $this->novoLogo->store(ContaActual::pastaLogos(), 'public');
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
            'novoMetodoNome' => ['required', 'string', 'max:255', Rule::unique('metodos_pagamento', 'nome')->ignore($this->editandoMetodoId)->where('conta_id', ContaActual::id())],
            'novoMetodoLogo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
        ]);

        $payload = ['nome' => $data['novoMetodoNome']];

        if ($this->novoMetodoLogo) {
            $payload['logo_path'] = $this->novoMetodoLogo->store(ContaActual::pastaLogos(), 'public');
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

    public function updatedAntecedencias(mixed $valor, string $ciclo): void
    {
        if (! array_key_exists($ciclo, ServicoStatusService::ANTECEDENCIA_PADRAO) || ! in_array((int) $valor, ServicoStatusService::ANTECEDENCIA_OPCOES, true)) {
            $this->antecedencias = array_merge(ServicoStatusService::ANTECEDENCIA_PADRAO, Configuracao::obter('lembretes', [])['antecedencia'] ?? []);

            return;
        }

        $lembretes = Configuracao::obter('lembretes', []);
        $lembretes['antecedencia'] = array_merge(ServicoStatusService::ANTECEDENCIA_PADRAO, $lembretes['antecedencia'] ?? [], [$ciclo => (int) $valor]);
        Configuracao::query()->updateOrCreate(['chave' => 'lembretes'], ['valor' => $lembretes]);

        // "A vencer" starts at the same lead time, so statuses follow straight away.
        app(ServicoStatusService::class)->sincronizarTodos();

        unset($this->lembretesGlobais);
        $plural = ['mensal' => 'mensais', 'trimestral' => 'trimestrais', 'semestral' => 'semestrais', 'anual' => 'anuais'][$ciclo];
        $this->dispatch('toast', title: 'Aviso actualizado', body: sprintf('Serviços %s: o primeiro aviso passa a ser %d %s antes.', $plural, (int) $valor, (int) $valor === 1 ? 'dia' : 'dias'), tone: 'success');
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function antecedenciaOpcoes(): array
    {
        return array_map(
            fn (int $d) => ['value' => (string) $d, 'label' => $d === 1 ? '1 dia antes' : "{$d} dias antes"],
            ServicoStatusService::ANTECEDENCIA_OPCOES,
        );
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
            'novoLogoEmpresa' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
        ]);

        $empresa = Configuracao::obter('empresa', []);
        $empresa['nome'] = $data['empresaNome'];
        $empresa['email'] = $data['empresaEmail'];
        $empresa['moeda'] = $data['empresaMoeda'];
        $empresa['fuso_horario'] = $data['empresaFuso'];

        if ($this->novoLogoEmpresa) {
            $empresa['logo_path'] = $this->novoLogoEmpresa->store(ContaActual::pastaLogos(), 'public');
        }

        Configuracao::query()->updateOrCreate(['chave' => 'empresa'], ['valor' => $empresa]);

        $this->novoLogoEmpresa = null;
        $this->dispatch('toast', title: 'Dados da empresa actualizados', tone: 'success');
    }

    public function guardarSmtp(): void
    {
        if (! $this->smtpProprioPermitido) {
            $this->dispatch('toast', title: 'Servidor de email próprio é do plano Pro', body: 'No teu plano os emails saem pelo servidor da Vencia, com o nome da tua empresa.', tone: 'info');

            return;
        }

        $data = $this->validate([
            'smtpServidor' => ['nullable', 'string', 'max:255'],
            'smtpPorta' => ['nullable', 'integer', 'between:1,65535'],
            'smtpSeguranca' => ['required', Rule::in(['STARTTLS', 'SSL', 'Nenhuma'])],
            'smtpUtilizador' => ['nullable', 'string', 'max:255'],
            'smtpPassword' => ['nullable', 'string', 'max:255'],
        ], [
            'smtpPorta.integer' => 'A porta tem de ser um número (normalmente 587 ou 465).',
            'smtpPorta.between' => 'A porta tem de estar entre 1 e 65535.',
        ]);

        $existente = Configuracao::obter('smtp', []);

        Configuracao::query()->updateOrCreate(['chave' => 'smtp'], [
            'valor' => [
                'servidor' => $data['smtpServidor'],
                'porta' => (string) $data['smtpPorta'],
                'seguranca' => $data['smtpSeguranca'],
                'utilizador' => $data['smtpUtilizador'],
                // Blank password field means "keep the one already saved" —
                // the field is never pre-filled (see its property docblock),
                // so an empty submit is not the admin asking to clear it.
                // Stored encrypted with APP_KEY: a database dump or backup
                // never exposes the mailbox password.
                'password' => $data['smtpPassword'] !== ''
                    ? Crypt::encryptString($data['smtpPassword'])
                    : ($existente['password'] ?? null),
            ],
        ]);

        $this->smtpPassword = '';
        $this->dispatch('toast', title: 'Configuração de SMTP guardada', tone: 'success');
    }

    /**
     * Sends one sample of the chosen email (EmailsDeTeste) to any address,
     * through the same runtime mailer the real sends use, so it proves both
     * that SMTP works and how each message looks to the client.
     */
    public function enviarTeste(): void
    {
        $this->validate([
            'testeTipo' => ['required', Rule::in(EmailsDeTeste::TIPOS_CONTA)],
            'testeDestino' => ['required', 'email'],
        ], [], ['testeTipo' => 'tipo de email', 'testeDestino' => 'email de destino']);

        $tipo = EmailsDeTeste::TIPOS[$this->testeTipo];

        try {
            app(EmailsDeTeste::class)->enviar($this->testeTipo, $this->testeDestino);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar email de teste ('.$this->testeTipo.'): '.$e->getMessage());
            $this->dispatch('toast', title: 'Falha ao enviar: '.$tipo, body: mb_strimwidth($e->getMessage(), 0, 160, '…'), tone: 'danger');

            return;
        }

        if (EmailsDeTeste::soNoRegisto()) {
            $this->dispatch('toast', title: 'SMTP por configurar', body: 'O email foi gravado só no registo (storage/logs), não saiu para '.$this->testeDestino.'.', tone: 'info');

            return;
        }

        $this->dispatch('toast', title: 'Email de teste enviado', body: $tipo.' → '.$this->testeDestino, tone: 'success');
    }

    /** Own SMTP is a paid feature; without it emails leave through the platform's server. */
    #[Computed]
    public function smtpProprioPermitido(): bool
    {
        return (bool) ContaActual::get()?->planoEfectivo()->permite('smtp_proprio');
    }

    public function render()
    {
        return view('livewire.configuracoes.configuracoes-index');
    }
}
