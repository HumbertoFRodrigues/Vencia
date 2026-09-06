@php
    $eventos = $this->eventosPorDia;
    $celulas = $this->celulas;
    $hoje = \Illuminate\Support\Carbon::today();
    $dows = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
@endphp

<div style="display:flex;flex-direction:column;gap:10px">
    <div style="display:flex;align-items:center;justify-content:space-between">
        <strong style="font-size:var(--text-body-size);color:var(--text-strong)">{{ $this->mesLabel }}</strong>
        <div style="display:inline-flex;align-items:center;gap:4px">
            <x-ui.icon-button icon="arrow-left" label="Mês anterior" size="sm" wire:click="anterior" />
            <x-ui.button size="sm" wire:click="hoje">Hoje</x-ui.button>
            <x-ui.icon-button icon="arrow-right" label="Mês seguinte" size="sm" wire:click="seguinte" />
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:var(--border-subtle);border:1px solid var(--border-subtle);border-radius:var(--radius-md);overflow:hidden">
        @foreach($dows as $d)
            <div style="background:var(--ink-25);padding:7px 9px;font-size:var(--text-label);letter-spacing:var(--text-label-ls);text-transform:uppercase;font-weight:var(--weight-semibold);color:var(--text-muted)">{{ $d }}</div>
        @endforeach

        @foreach($celulas as $dia)
            @php
                $isHoje = $dia && $ano === $hoje->year && $mes === $hoje->month && $dia === $hoje->day;
            @endphp
            <div style="min-height:78px;background:{{ $dia ? 'var(--surface-card)' : 'var(--ink-25)' }};padding:6px 7px;display:flex;flex-direction:column;gap:4px">
                @if($dia)
                    <span class="num" style="font-size:var(--text-xs);color:{{ $isHoje ? 'var(--text-strong)' : 'var(--text-faint)' }};font-weight:{{ $isHoje ? 600 : 400 }}">{{ str_pad((string) $dia, 2, '0', STR_PAD_LEFT) }}</span>
                    @foreach($eventos[$dia] ?? [] as $s)
                        @php
                            [$bgVar, $fgVar] = match ($s->status->value) {
                                'vencido' => ['--status-overdue-bg', '--status-overdue-fg'],
                                'a_vencer' => ['--status-due-bg', '--status-due-fg'],
                                default => ['--status-active-bg', '--status-active-fg'],
                            };
                        @endphp
                        <a href="{{ route('servicos.show', $s) }}" wire:navigate style="display:flex;align-items:center;gap:5px;text-decoration:none;background:var({{ $bgVar }});color:var({{ $fgVar }});border-radius:var(--radius-xs);padding:3px 5px;font-size:11px;overflow:hidden">
                            <x-ui.service-logo :name="$s->nome" :category="$s->categoria" size="14" />
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ explode(' ', $s->cliente?->nome ?? '—')[0] }} — {{ $s->nome }}</span>
                        </a>
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
</div>
