<?php

namespace App\Livewire\Vencimentos;

use App\Enums\ServicoStatus;
use App\Models\Servico;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Self-contained 7-column month grid — owns its own prev/next/hoje
 * navigation state so it can sit inside VencimentosIndex's "Calendário"
 * view without the parent knowing which month is showing (per the brief:
 * "own prev/next month navigation state").
 */
class CalendarioMes extends Component
{
    public int $ano;

    public int $mes;

    public function mount(): void
    {
        $this->ano = (int) now()->year;
        $this->mes = (int) now()->month;
    }

    public function anterior(): void
    {
        $data = Carbon::create($this->ano, $this->mes, 1)->subMonthNoOverflow();
        $this->ano = $data->year;
        $this->mes = $data->month;
    }

    public function seguinte(): void
    {
        $data = Carbon::create($this->ano, $this->mes, 1)->addMonthNoOverflow();
        $this->ano = $data->year;
        $this->mes = $data->month;
    }

    public function hoje(): void
    {
        $this->ano = (int) now()->year;
        $this->mes = (int) now()->month;
    }

    #[Computed]
    public function mesLabel(): string
    {
        return ucfirst(Carbon::create($this->ano, $this->mes, 1)->locale('pt_PT')->translatedFormat('F')).' de '.$this->ano;
    }

    /**
     * Every non-cancelled serviço whose vencimento falls in the displayed
     * month, grouped by day-of-month — suspenso stays visible here too
     * (same reasoning as VencimentosIndex's list tabs: the date is still
     * informative even though reminders stopped).
     *
     * @return array<int, list<Servico>>
     */
    #[Computed]
    public function eventosPorDia(): array
    {
        $inicio = Carbon::create($this->ano, $this->mes, 1)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        $servicos = Servico::query()
            ->with('cliente')
            ->whereNotNull('vencimento')
            ->whereBetween('vencimento', [$inicio->toDateString(), $fim->toDateString()])
            ->where('status', '!=', ServicoStatus::Cancelado)
            ->orderBy('vencimento')
            ->get();

        $porDia = [];

        foreach ($servicos as $s) {
            $porDia[$s->vencimento->day][] = $s;
        }

        return $porDia;
    }

    /** @return list<int|null> Calendar cells: leading nulls for the Monday-first offset, then 1..last day of month. */
    #[Computed]
    public function celulas(): array
    {
        $inicio = Carbon::create($this->ano, $this->mes, 1);
        $offset = $inicio->dayOfWeekIso - 1; // Monday-first week (Seg..Dom)
        $diasNoMes = $inicio->daysInMonth;

        $celulas = array_fill(0, $offset, null);

        for ($d = 1; $d <= $diasNoMes; $d++) {
            $celulas[] = $d;
        }

        return $celulas;
    }

    public function render()
    {
        return view('livewire.vencimentos.calendario-mes');
    }
}
