<?php

namespace App\Livewire\Pessoal\Concerns;

use App\Services\FinancasPessoais;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/** Month selector shared by the personal pages, kept in the URL (?mes=2026-10) so a refresh or a link keeps the month. */
trait NavegaMeses
{
    #[Url(except: '')]
    public string $mes = '';

    public function mesActual(): Carbon
    {
        return FinancasPessoais::mesDe($this->mes);
    }

    public function mudarMes(int $delta): void
    {
        $alvo = $this->mesActual()->addMonthsNoOverflow($delta);
        $this->mes = $alvo->isSameMonth(Carbon::today()) ? '' : $alvo->format('Y-m');
    }

    public function irParaHoje(): void
    {
        $this->mes = '';
    }

    public function rotuloMes(): string
    {
        $m = $this->mesActual();

        return ucfirst($m->locale('pt_PT')->translatedFormat('F')).' de '.$m->year;
    }
}
