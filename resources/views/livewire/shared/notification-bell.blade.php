@php
    use App\Enums\ServicoStatus;
    use Illuminate\Support\Facades\Route;

    $alertas = $this->alertas;
@endphp

<div class="top-bar__bell-wrap dropdown">
    <x-ui.icon-button icon="bell" label="Notificações" data-dropdown-toggle data-dropdown-group="notificacoes" aria-haspopup="true" aria-expanded="false" />
    @if($alertas->isNotEmpty())<span class="top-bar__badge">{{ $alertas->count() }}</span>@endif

    <div class="dropdown-panel notification-panel" data-dropdown-panel data-dropdown-group="notificacoes" hidden>
        <div class="notification-panel__header">Alertas</div>

        @if($alertas->isEmpty())
            <div class="notification-panel__empty">Não há serviços vencidos ou a vencer.</div>
        @else
            <div class="notification-panel__list">
                @foreach($alertas as $i => $s)
                    @php
                        $href = Route::has('servicos.show') ? route('servicos.show', $s) : '#';
                        $dias = $s->dias;
                        $vencido = $s->status === ServicoStatus::Vencido;
                        $texto = $vencido
                            ? $s->nome.' está vencido'.($dias !== null ? ' há '.abs($dias).' '.(abs($dias) === 1 ? 'dia' : 'dias') : '')
                            : $s->nome.' vence em '.$dias.' '.($dias === 1 ? 'dia' : 'dias');
                    @endphp
                    <a href="{{ $href }}" wire:navigate class="notification-panel__item" style="{{ $i ? 'border-top:1px solid var(--border-subtle);' : '' }}">
                        <x-ui.icon :name="$vencido ? 'circle-alert' : 'calendar-clock'" size="15" :color="$vencido ? 'var(--status-overdue-fg)' : 'var(--status-due-fg)'" />
                        <span class="notification-panel__item-text">{{ $texto }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <a href="{{ Route::has('vencimentos.index') ? route('vencimentos.index') : '#' }}" wire:navigate class="notification-panel__footer">
            Ver todos os vencimentos
        </a>
    </div>
</div>
