<?php

namespace App\Livewire\Servicos;

use App\Enums\ClienteStatus;
use App\Enums\Periodicidade;
use App\Enums\Periodo;
use App\Models\BibliotecaServico;
use App\Models\Cliente;
use App\Models\Historico;
use App\Models\Servico;
use App\Models\ServicoLembreteConfig;
use App\Services\ServicoStatusService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * "Nova assinatura" dialog. There is no separate nome/categoria input: the
 * biblioteca pill-strip pick *is* the source of truth for those two fields
 * (matches NewSubscriptionDialog.jsx exactly — nome/categoria only ever
 * come from the selected BibliotecaServico there too). The cliente picker
 * doubles as an inline "+ Novo cliente" creator, created in the same
 * transaction as the Servico — no need to reuse ClienteForm here, per the
 * brief, since the prototype's own dialog does it inline as well.
 */
class NewSubscriptionDialog extends Component
{
    /** @var list<array{value: string, label: string}> */
    private const PERIODICIDADE_OPTIONS = [
        ['value' => 'mensal', 'label' => 'Mensal'],
        ['value' => 'trimestral', 'label' => 'Trimestral'],
        ['value' => 'semestral', 'label' => 'Semestral'],
        ['value' => 'anual', 'label' => 'Anual'],
        ['value' => 'unica', 'label' => 'Única'],
        ['value' => 'personalizada', 'label' => 'Personalizada'],
    ];

    /** @var list<string> */
    private const METODOS = ['mpesa', 'emola', 'transferencia', 'dinheiro', 'outro'];

    public bool $show = false;

    public ?int $bibliotecaServicoId = null;

    public string $clienteSelecionado = '';

    public string $novoClienteNome = '';

    public string $novoClienteEmail = '';

    public string $valor = '';

    public string $periodicidade = 'mensal';

    public string $inicio = '';

    public ?string $duracaoDias = null;

    /**
     * Optional manual override of the calculated vencimento — for a serviço
     * the admin is entering that already exists in real life (a client
     * migrated into the system), where "início + periodicidade" doesn't
     * land on the real due date. Left empty, vencimentoCalculado() is used
     * as before; filled, it wins outright.
     */
    public string $vencimentoManual = '';

    public string $descricao = '';

    public string $metodoHabitual = 'mpesa';

    #[On('assinatura:abrir')]
    public function abrir(?int $clienteId = null): void
    {
        $this->resetValidation();
        $this->reset(['novoClienteNome', 'novoClienteEmail', 'valor', 'duracaoDias', 'descricao', 'vencimentoManual']);

        $primeira = BibliotecaServico::query()->where('arquivado', false)->orderBy('nome')->first();
        $this->bibliotecaServicoId = $primeira?->id;
        $this->descricao = $primeira?->descricao_padrao ?? '';

        $this->clienteSelecionado = $clienteId !== null
            ? (string) $clienteId
            : (string) (Cliente::query()->orderBy('nome')->value('id') ?? '');

        $this->periodicidade = 'mensal';
        $this->inicio = Carbon::today()->toDateString();
        $this->metodoHabitual = 'mpesa';

        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    public function escolherBiblioteca(int $bibliotecaServicoId): void
    {
        $this->bibliotecaServicoId = $bibliotecaServicoId;
        $item = BibliotecaServico::find($bibliotecaServicoId);
        $this->descricao = $item?->descricao_padrao ?? '';
    }

    /** @return list<string> */
    #[Computed]
    public function metodos(): array
    {
        return self::METODOS;
    }

    /** @return Collection<int, BibliotecaServico> */
    #[Computed]
    public function biblioteca(): Collection
    {
        return BibliotecaServico::query()->where('arquivado', false)->orderBy('nome')->get();
    }

    #[Computed]
    public function bibliotecaSelecionada(): ?BibliotecaServico
    {
        return $this->bibliotecaServicoId ? BibliotecaServico::find($this->bibliotecaServicoId) : null;
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function clientesOptions(): array
    {
        $opcoes = Cliente::query()->orderBy('nome')->get()
            ->map(fn (Cliente $c) => ['value' => (string) $c->id, 'label' => $c->nome])
            ->all();

        $opcoes[] = ['value' => '__novo', 'label' => '+ Novo cliente'];

        return $opcoes;
    }

    #[Computed]
    public function novoCliente(): bool
    {
        return $this->clienteSelecionado === '__novo';
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function periodicidadeOptions(): array
    {
        return self::PERIODICIDADE_OPTIONS;
    }

    /**
     * Live preview: same date math ServicoStatusService uses for renewals
     * (calcularVencimentoInicial() shares its helper with
     * calcularProximoVencimento()), applied to the chosen início instead of
     * an existing vencimento since this servico doesn't exist yet.
     */
    #[Computed]
    public function vencimentoCalculado(): ?Carbon
    {
        if ($this->inicio === '' || Periodicidade::tryFrom($this->periodicidade) === null) {
            return null;
        }

        $duracaoDias = $this->duracaoDias !== null && $this->duracaoDias !== '' ? (int) $this->duracaoDias : null;

        return app(ServicoStatusService::class)->calcularVencimentoInicial(
            Carbon::parse($this->inicio),
            Periodicidade::from($this->periodicidade),
            $duracaoDias,
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = [
            'bibliotecaServicoId' => ['required', 'integer', Rule::exists('biblioteca_servicos', 'id')],
            'clienteSelecionado' => ['required', 'string'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'periodicidade' => ['required', Rule::enum(Periodicidade::class)],
            'inicio' => ['required', 'date'],
            'vencimentoManual' => ['nullable', 'date'],
            'duracaoDias' => [$this->periodicidade === 'personalizada' ? 'required' : 'nullable', 'integer', 'min:1'],
            'descricao' => ['nullable', 'string'],
            'metodoHabitual' => ['required', 'string'],
        ];

        if ($this->novoCliente) {
            $rules['novoClienteNome'] = ['required', 'string', 'max:255'];
            $rules['novoClienteEmail'] = ['required', 'string', 'email', 'max:255', Rule::unique('clientes', 'email')];
        }

        return $rules;
    }

    public function guardar(): void
    {
        $data = $this->validate();

        $servico = DB::transaction(function () use ($data) {
            if ($this->novoCliente) {
                $cliente = Cliente::create([
                    'nome' => $data['novoClienteNome'],
                    'email' => $data['novoClienteEmail'],
                    'status' => ClienteStatus::Activo,
                    'desde' => Carbon::today()->toDateString(),
                ]);
            } else {
                $cliente = Cliente::findOrFail((int) $this->clienteSelecionado);
            }

            $bibliotecaServico = BibliotecaServico::findOrFail($this->bibliotecaServicoId);
            $periodicidade = Periodicidade::from($this->periodicidade);
            $duracaoDias = $periodicidade === Periodicidade::Personalizada ? (int) $this->duracaoDias : null;
            $vencimento = trim($this->vencimentoManual) !== ''
                ? Carbon::parse($this->vencimentoManual)
                : $this->vencimentoCalculado;

            $servico = Servico::create([
                'cliente_id' => $cliente->id,
                'biblioteca_servico_id' => $bibliotecaServico->id,
                'nome' => $bibliotecaServico->nome,
                'descricao' => trim((string) $this->descricao) !== '' ? $this->descricao : null,
                'categoria' => $bibliotecaServico->categoria,
                'valor' => $this->valor,
                'periodo' => $this->periodoNormalizado($periodicidade, $duracaoDias)->value,
                'periodicidade' => $periodicidade,
                'duracao_dias' => $duracaoDias,
                'inicio' => $this->inicio,
                'vencimento' => $vencimento?->toDateString(),
                'status' => 'activo',
                'metodo_habitual' => $this->metodoHabitual,
            ]);

            ServicoLembreteConfig::create(['servico_id' => $servico->id]);

            Historico::create([
                'cliente_id' => $cliente->id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Serviço criado',
                'description' => sprintf('%s — %s', $servico->nome, $cliente->nome),
                'kind' => 'criado',
            ]);

            Historico::create([
                'cliente_id' => $cliente->id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Serviço activado',
                'description' => null,
                'kind' => 'activado',
            ]);

            return $servico;
        });

        $this->show = false;
        $this->dispatch('servico-criado', servicoId: $servico->id);
        $this->dispatch('toast', title: 'Assinatura criada', body: $servico->nome.' — '.$servico->cliente->nome, tone: 'success');
    }

    /**
     * "Periodo" only normalises the billing cadence for the "/mês"-vs-"/ano"
     * display label (MRR math itself uses periodicidade, per Periodo's own
     * docblock) — personalizada picks whichever reads more naturally for
     * its actual duration, única is unused for display since MoneyValue's
     * period suffix is omitted for it wherever it's rendered.
     */
    private function periodoNormalizado(Periodicidade $periodicidade, ?int $duracaoDias): Periodo
    {
        return match ($periodicidade) {
            Periodicidade::Mensal => Periodo::Mes,
            Periodicidade::Trimestral, Periodicidade::Semestral, Periodicidade::Anual => Periodo::Ano,
            Periodicidade::Personalizada => ($duracaoDias ?? 0) <= 45 ? Periodo::Mes : Periodo::Ano,
            Periodicidade::Unica => Periodo::Ano,
        };
    }

    public function render()
    {
        return view('livewire.servicos.new-subscription-dialog');
    }
}
