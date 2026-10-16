<?php

namespace App\Livewire;

use App\Enums\ServicoStatus;
use App\Livewire\Concerns\RespeitaPlano;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Convite;
use App\Models\Servico;
use App\Services\RelatorioPdf;
use App\Services\Totais;
use App\Support\ContaActual;
use App\Support\Csv;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    use RespeitaPlano;

    /**
     * @return array{
     *     entradas: float, nPagamentos: int,
     *     aReceber: float, nAReceber: int,
     *     emAtraso: float, nEmAtraso: int,
     *     aVencer: int,
     *     clientesActivos: int, servicosActivos: int,
     *     mrr: float, arr: float, esperada: float,
     * }
     */
    #[Computed]
    public function totais(): array
    {
        return app(Totais::class)->calcular();
    }

    /**
     * "Primeiros passos" for a new business: each step ticks itself off from
     * real data. Hidden once everything is done or the user dismisses it.
     *
     * @return list<array{feito: bool, titulo: string, texto: string, accao: string, href: ?string, evento: ?string}>|null
     */
    #[Computed]
    public function primeirosPassos(): ?array
    {
        if (Configuracao::obter('onboarding', [])['dispensado'] ?? false) {
            return null;
        }

        $empresa = Configuracao::obter('empresa', []);
        $gere = Gate::allows('gerir-negocio');
        $dono = Gate::allows('gerir-conta');

        $passos = [];
        if ($gere) {
            $passos[] = [
                'feito' => filled($empresa['email'] ?? null) && filled($empresa['logo_path'] ?? null),
                'titulo' => 'Completa os dados da empresa',
                'texto' => 'Email e logótipo aparecem nos lembretes e recibos que os teus clientes recebem.',
                'accao' => 'Abrir Configurações',
                'href' => route('configuracoes.index', ['tab' => 'empresa']),
                'evento' => null,
            ];
        }
        $passos[] = [
            'feito' => Cliente::query()->exists(),
            'titulo' => 'Regista o primeiro cliente',
            'texto' => 'Nome e email. Podes importar os outros aos poucos.',
            'accao' => 'Ir para Clientes',
            'href' => route('clientes.index'),
            'evento' => null,
        ];
        $passos[] = [
            'feito' => Servico::query()->exists(),
            'titulo' => 'Cria o primeiro serviço',
            'texto' => 'O valor e quando renova. A partir daí os lembretes saem sozinhos.',
            'accao' => 'Nova assinatura',
            'href' => null,
            'evento' => 'assinatura:abrir',
        ];
        if ($dono) {
            $passos[] = [
                'feito' => ContaActual::get()->membros()->count() > 1 || Convite::query()->where('conta_id', ContaActual::id())->exists(),
                'titulo' => 'Convida a tua equipa (opcional)',
                'texto' => 'Quem regista pagamentos não precisa de ver as finanças.',
                'accao' => 'Abrir Equipa',
                'href' => route('conta.equipa'),
                'evento' => null,
            ];
        }

        return collect($passos)->every(fn ($p) => $p['feito']) ? null : $passos;
    }

    public function dispensarPrimeirosPassos(): void
    {
        Configuracao::guardar('onboarding', ['dispensado' => true]);
        unset($this->primeirosPassos);
    }

    /**
     * Próximos vencimentos — every non-suspended, non-cancelled service,
     * nearest due date first.
     *
     * @return Collection<int, Servico>
     */
    #[Computed]
    public function proximosVencimentos(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereNotIn('status', [ServicoStatus::Cancelado, ServicoStatus::Suspenso])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->limit(8)
            ->get();
    }

    /**
     * Alerts: services that are overdue or due soon.
     *
     * @return Collection<int, Servico>
     */
    #[Computed]
    public function alertas(): Collection
    {
        return Servico::query()
            ->with('cliente')
            ->whereIn('status', [ServicoStatus::Vencido, ServicoStatus::AVencer])
            ->orderByRaw('vencimento IS NULL, vencimento ASC')
            ->limit(8)
            ->get();
    }

    /**
     * Exports the same "próximos vencimentos" list shown on this screen —
     * the only single dataset the Dashboard itself owns (the metric tiles
     * are aggregates, not a row list, so there's nothing else to export
     * here). Mirrors PagamentosIndex::exportarCsv()'s exact CSV conventions
     * (UTF-8 BOM, semicolon delimiter, dd/mm/aaaa dates, dot-thousands MZN).
     */
    public function exportarCsv(): ?StreamedResponse
    {
        if ($this->bloqueadoPeloPlano('exportar')) {
            return null;
        }

        $servicos = $this->proximosVencimentos;

        $this->dispatch('toast', title: 'A exportar CSV', body: 'A transferência começa em breve.', tone: 'success');

        return Csv::download(
            'proximos_vencimentos_'.now()->format('Y-m-d_His').'.csv',
            ['Serviço', 'Cliente', 'Vencimento', 'Valor', 'Estado'],
            $servicos->map(fn ($s) => [
                $s->nome,
                $s->cliente?->nome ?? '',
                $s->vencimento?->format('d/m/Y') ?? '',
                number_format((float) $s->valor, 0, ',', '.').' '.Configuracao::moeda(),
                $this->statusLabel($s->status),
            ]),
        );
    }

    /** Same "próximos vencimentos" rows as exportarCsv(), through the shared relatório template. */
    public function exportarPdf(): ?StreamedResponse
    {
        if ($this->bloqueadoPeloPlano('exportar')) {
            return null;
        }

        $servicos = $this->proximosVencimentos;

        $linhas = $servicos->map(fn (Servico $s) => [
            $s->nome,
            $s->cliente?->nome ?? '',
            $s->vencimento?->format('d/m/Y') ?? '',
            RelatorioPdf::moeda((float) $s->valor),
            $this->statusLabel($s->status),
        ])->all();

        $this->dispatch('toast', title: 'A exportar PDF', body: 'A transferência começa em breve.', tone: 'success');

        return RelatorioPdf::gerar(
            titulo: 'Próximos Vencimentos',
            subtitulo: $servicos->count() === 1
                ? 'O serviço mais próximo do vencimento'
                : 'Os '.$servicos->count().' serviços mais próximos do vencimento',
            colunas: ['Serviço', 'Cliente', 'Vencimento', 'Valor', 'Estado'],
            linhas: $linhas,
            nomeArquivo: 'proximos_vencimentos_'.now()->format('Y-m-d_His').'.pdf',
            colunasNumericas: [3],
        );
    }

    /** Mirrors StatusBadge's label map (that one's LABELS const is private to the component). */
    private function statusLabel(ServicoStatus $status): string
    {
        return match ($status) {
            ServicoStatus::Activo => 'Activo',
            ServicoStatus::AVencer => 'A vencer',
            ServicoStatus::Vencido => 'Vencido',
            ServicoStatus::Suspenso => 'Suspenso',
            ServicoStatus::Cancelado => 'Cancelado',
        };
    }

    #[On('servico-criado')]
    #[On('servico-actualizado')]
    public function actualizar(): void
    {
        unset($this->totais, $this->proximosVencimentos, $this->alertas);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
