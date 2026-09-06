<?php

namespace App\Livewire\Servicos;

use App\Enums\IntervaloLembrete;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\Servico;
use App\Models\Suspensao;
use App\Services\ServicoStatusService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ServicoDetail extends Component
{
    /** @var array<string, string> */
    private const INTERVALO_LABELS = [
        'd30' => '30 dias antes',
        'd15' => '15 dias antes',
        'd7' => '7 dias antes',
        'd3' => '3 dias antes',
        'd1' => '1 dia antes',
        'no_dia' => 'No dia',
        'apos_vencimento' => 'Após vencimento',
    ];

    public Servico $servico;

    public function mount(Servico $servico): void
    {
        $this->servico = $servico->load('cliente', 'lembreteConfig');
    }

    /** @return Collection<int, Historico> */
    #[Computed]
    public function historicos(): Collection
    {
        return Historico::query()
            ->where('servico_id', $this->servico->id)
            ->orderByDesc('occurred_at')
            ->get();
    }

    #[Computed]
    public function ultimaSuspensao(): ?Suspensao
    {
        return $this->servico->suspensoes()->orderByDesc('data')->orderByDesc('id')->first();
    }

    /**
     * Nearest enabled reminder interval still ahead of the current "dias"
     * countdown — the a_vencer alert banner's "próximo aviso automático"
     * line reuses ServicoStatusService's own two-tier config resolution
     * instead of re-deriving it here.
     */
    #[Computed]
    public function proximoAvisoDias(): ?int
    {
        if ($this->servico->dias === null) {
            return null;
        }

        return app(ServicoStatusService::class)->proximoIntervaloAtivo($this->servico, $this->servico->dias);
    }

    /**
     * Effective (resolved) on/off state per reminder interval — per-servico
     * override if set, else the global default — this is what the
     * checkboxes must visually reflect (tri-state null=inherit lives only
     * in the DB row; the UI always shows a concrete boolean).
     *
     * @return array<string, bool>
     */
    #[Computed]
    public function lembretesEfetivos(): array
    {
        $config = $this->servico->lembreteConfig;
        $globais = Configuracao::obter('lembretes', []);
        $efetivos = [];

        foreach (IntervaloLembrete::cases() as $intervalo) {
            $efetivos[$intervalo->value] = $config?->{$intervalo->value} ?? (bool) ($globais[$intervalo->value] ?? false);
        }

        return $efetivos;
    }

    /** @return array<string, string> */
    #[Computed]
    public function intervaloLabels(): array
    {
        return self::INTERVALO_LABELS;
    }

    /**
     * Toggling always writes an explicit true/false to the per-servico
     * override row (flipping the currently effective/resolved value) — once
     * touched from this screen a flag never goes back to null (inherit).
     */
    public function alternarLembrete(string $intervalo): void
    {
        if (! array_key_exists($intervalo, self::INTERVALO_LABELS)) {
            return;
        }

        $efetivoActual = $this->lembretesEfetivos[$intervalo];
        $config = $this->servico->lembreteConfig ?? $this->servico->lembreteConfig()->create([]);

        $config->update([$intervalo => ! $efetivoActual]);

        $this->servico->load('lembreteConfig');
        unset($this->lembretesEfetivos);
    }

    #[On('servico-actualizado')]
    public function servicoActualizado(int $servicoId): void
    {
        if ($servicoId === $this->servico->id) {
            $this->servico->refresh();
            $this->servico->load('cliente', 'lembreteConfig');
            unset($this->historicos, $this->ultimaSuspensao, $this->proximoAvisoDias, $this->lembretesEfetivos);
        }
    }

    public function render()
    {
        // Title depends on the serviço's name, so it's set fluently here
        // rather than via the static #[Title('...')] attribute (same
        // convention as ClienteDetail).
        return view('livewire.servicos.servico-detail')
            ->layout('layouts.app')
            ->title($this->servico->nome.' · Vencia');
    }
}
