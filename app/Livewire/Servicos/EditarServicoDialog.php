<?php

namespace App\Livewire\Servicos;

use App\Enums\HistoricoKind;
use App\Enums\Periodicidade;
use App\Enums\Periodo;
use App\Enums\ServicoCategoria;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Servico;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * "Editar serviço" dialog — same family as RenovarDialog/SuspenderDialog
 * (only servicoId kept as state, servico re-fetched on abrir()), but unlike
 * those two this is a plain field edit, not a status-machine transition: it
 * never touches status, cliente_id or biblioteca_servico_id, and — unlike
 * NewSubscriptionDialog — it never recomputes vencimento from início. An
 * existing serviço's vencimento may already reflect past Renovar actions, so
 * the admin sees and corrects the real stored date directly; only an
 * explicit edit of that field changes it.
 */
class EditarServicoDialog extends Component
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

    /** Field => human label, used both for change-detection and the Histórico description. */
    private const ROTULOS = [
        'nome' => 'Nome',
        'descricao' => 'Descrição',
        'plano' => 'Plano',
        'categoria' => 'Categoria',
        'valor' => 'Valor',
        'periodicidade' => 'Periodicidade',
        'duracao_dias' => 'Duração',
        'inicio' => 'Início',
        'vencimento' => 'Vencimento',
        'metodo_habitual' => 'Método habitual',
    ];

    public bool $show = false;

    public ?int $servicoId = null;

    public string $nome = '';

    public string $descricao = '';

    public ?string $plano = null;

    public string $categoria = 'outro';

    public string $valor = '';

    public string $periodicidade = 'mensal';

    public ?string $duracaoDias = null;

    public string $inicio = '';

    public ?string $vencimento = null;

    public string $metodoHabitual = 'mpesa';

    #[On('servico-editar:abrir')]
    public function abrir(int $servicoId): void
    {
        $this->resetValidation();
        $servico = Servico::findOrFail($servicoId);

        $this->servicoId = $servicoId;
        $this->nome = $servico->nome;
        $this->descricao = (string) $servico->descricao;
        $this->plano = $servico->plano;
        $this->categoria = $servico->categoria->value;
        $this->valor = (string) $servico->valor;
        $this->periodicidade = $servico->periodicidade->value;
        $this->duracaoDias = $servico->duracao_dias !== null ? (string) $servico->duracao_dias : null;
        $this->inicio = $servico->inicio->toDateString();
        $this->vencimento = $servico->vencimento?->toDateString();
        $this->metodoHabitual = $servico->metodo_habitual ?? 'mpesa';

        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    /** Every active (non-arquivado) método, well-known plus any custom one the admin has added. @return list<string> */
    #[Computed]
    public function metodos(): array
    {
        return MetodoPagamentoOpcao::nomesActivos();
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

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function periodicidadeOptions(): array
    {
        return self::PERIODICIDADE_OPTIONS;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'plano' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', Rule::enum(ServicoCategoria::class)],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'periodicidade' => ['required', Rule::enum(Periodicidade::class)],
            'duracaoDias' => [$this->periodicidade === 'personalizada' ? 'required' : 'nullable', 'integer', 'min:1'],
            'inicio' => ['required', 'date'],
            'vencimento' => ['nullable', 'date'],
            'metodoHabitual' => ['required', 'string'],
        ];
    }

    public function guardar(): void
    {
        $data = $this->validate();
        $servico = Servico::findOrFail($this->servicoId);

        $periodicidade = Periodicidade::from($data['periodicidade']);
        $duracaoDias = $periodicidade === Periodicidade::Personalizada ? (int) $data['duracaoDias'] : null;

        $novosValores = [
            'nome' => $data['nome'],
            'descricao' => trim((string) $data['descricao']) !== '' ? $data['descricao'] : null,
            'plano' => $data['plano'] !== null && trim($data['plano']) !== '' ? $data['plano'] : null,
            'categoria' => $data['categoria'],
            'valor' => $data['valor'],
            // "periodo" is a derived display-only field (see Periodo's own
            // docblock) — kept in sync here the same way NewSubscriptionDialog
            // derives it on create, so editing periodicidade doesn't leave the
            // "/mês" vs "/ano" suffix stale. It is not user-editable directly.
            'periodo' => $this->periodoNormalizado($periodicidade, $duracaoDias)->value,
            'periodicidade' => $periodicidade->value,
            'duracao_dias' => $duracaoDias,
            'inicio' => $data['inicio'],
            'vencimento' => $data['vencimento'] !== null && $data['vencimento'] !== '' ? $data['vencimento'] : null,
            'metodo_habitual' => $data['metodoHabitual'],
        ];

        $alteracoes = $this->descreverAlteracoes($servico, $novosValores);

        $servico->update($novosValores);

        if ($alteracoes !== []) {
            Historico::create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => Carbon::now(),
                'title' => 'Serviço editado',
                'description' => implode('; ', $alteracoes),
                'kind' => HistoricoKind::Alterado,
            ]);
        }

        $this->show = false;
        $this->dispatch('servico-actualizado', servicoId: $servico->id);
        $this->dispatch('toast', title: 'Serviço actualizado', body: $servico->nome, tone: 'success');
    }

    /**
     * Compares the serviço's current attribute values against the values
     * about to be saved (called before ->update(), while $servico's
     * attributes are still the pre-edit ones) and returns one human-readable
     * "Campo: antigo → novo" line per field that actually changed — never
     * every field, per the brief.
     *
     * @param  array<string, mixed>  $novosValores
     * @return list<string>
     */
    private function descreverAlteracoes(Servico $servico, array $novosValores): array
    {
        $alteracoes = [];

        foreach (self::ROTULOS as $campo => $rotulo) {
            $antigo = $this->valorExibicao($campo, $servico->getAttribute($campo));
            $novo = $this->valorExibicao($campo, $novosValores[$campo] ?? null);

            if ($antigo === $novo) {
                continue;
            }

            $alteracoes[] = sprintf('%s: %s → %s', $rotulo, $antigo, $novo);
        }

        return $alteracoes;
    }

    /**
     * Human display string for one field's value, whichever shape it comes
     * in (a cast enum/Carbon straight off the model, or the raw string from
     * validated form data) — so the "before" and "after" side of a
     * descreverAlteracoes() comparison always render identically when the
     * underlying value didn't actually change.
     */
    private function valorExibicao(string $campo, mixed $valor): string
    {
        $valor = match ($campo) {
            'categoria' => $valor instanceof ServicoCategoria ? $valor : ServicoCategoria::tryFrom((string) $valor),
            'periodicidade' => $valor instanceof Periodicidade ? $valor : Periodicidade::tryFrom((string) $valor),
            default => $valor,
        };

        if ($valor instanceof ServicoCategoria || $valor instanceof Periodicidade) {
            return $valor->label();
        }

        if ($valor instanceof \BackedEnum) {
            $valor = $valor->value;
        }

        return match ($campo) {
            'valor' => $valor !== null && $valor !== '' ? number_format((float) $valor, 2, ',', '.').' '.Configuracao::moeda() : '—',
            'inicio', 'vencimento' => $this->paraData($valor)?->format('d/m/Y') ?? '—',
            'duracao_dias' => $valor !== null && $valor !== '' ? $valor.' dias' : '—',
            default => $valor !== null && trim((string) $valor) !== '' ? (string) $valor : '—',
        };
    }

    private function paraData(mixed $valor): ?Carbon
    {
        if ($valor instanceof Carbon) {
            return $valor;
        }

        if ($valor === null || $valor === '') {
            return null;
        }

        return Carbon::parse($valor);
    }

    /**
     * Same mapping NewSubscriptionDialog uses on create (kept here rather
     * than shared since it's a tiny, purely presentational derivation, not
     * business logic — ServicoStatusService is off-limits for this task).
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
        return view('livewire.servicos.editar-servico-dialog');
    }
}
