@php
    $n = $this->contagens;
@endphp
<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Faturação">
        <x-slot:actions>
            <x-ui.button size="sm" icon="download" wire:click="exportarCsv">Exportar CSV</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.tabs :active="$estado" :tabs="[
        ['id' => 'em_analise', 'label' => 'Por confirmar', 'count' => $n['em_analise'] ?? 0],
        ['id' => 'pendente', 'label' => 'À espera do pagamento', 'count' => $n['pendente'] ?? 0],
        ['id' => 'paga', 'label' => 'Pagas', 'count' => $n['paga'] ?? 0],
        ['id' => 'rejeitada', 'label' => 'Rejeitadas', 'count' => $n['rejeitada'] ?? 0],
        ['id' => 'todas', 'label' => 'Todas'],
    ]" />

    <x-ui.card :padding="0">
        <x-ui.data-table :columns="[['header' => 'Fatura'], ['header' => 'Conta'], ['header' => 'Plano'], ['header' => 'Valor', 'align' => 'right'], ['header' => 'Pagamento'], ['header' => 'Estado'], ['header' => '', 'align' => 'right']]" :empty="$this->faturas->isEmpty()">
            <x-slot:emptyState><x-ui.empty-state icon="circle-check" title="Nada aqui" /></x-slot:emptyState>
            @foreach($this->faturas as $f)
                <tr wire:key="fa-{{ $f->id }}">
                    <td class="num">{{ $f->referenciaInterna() }}<span class="lista-simples__meta" style="display:block">{{ $f->created_at->format('d/m/Y') }}</span></td>
                    <td><a href="{{ route('admin.contas.show', $f->conta_id) }}" wire:navigate>{{ $f->conta?->nome }}</a><span class="lista-simples__meta" style="display:block">{{ $f->pedidaPor?->email }}</span></td>
                    <td>{{ $f->plano?->nome }} · {{ \App\Services\Faturacao::CICLOS[$f->ciclo] ?? $f->ciclo }}</td>
                    <td class="u-text-right num">{{ $faturacao->valor($f) }}</td>
                    <td>
                        @if($f->metodo){{ $f->metodoLabel() }}@endif
                        @if($f->referencia)<span class="lista-simples__meta" style="display:block">ref. {{ $f->referencia }}</span>@endif
                        @if($url = $faturacao->urlComprovativo($f))<a href="{{ $url }}" target="_blank" rel="noopener">ver comprovativo</a>@endif
                    </td>
                    <td>
                        <x-ui.tag :tone="$f->estadoTom()">{{ $f->estadoLabel() }}</x-ui.tag>
                        @if($f->estado === 'paga' && $f->periodo_fim)<span class="lista-simples__meta" style="display:block">até {{ $f->periodo_fim->format('d/m/Y') }}</span>@endif
                        @if($f->estado === 'rejeitada' && $f->nota)<span class="lista-simples__meta" style="display:block">{{ $f->nota }}</span>@endif
                    </td>
                    <td class="u-text-right">
                        @if($f->aberta())
                            <div class="lista-simples__accoes" style="justify-content:flex-end">
                                <x-ui.button size="sm" variant="primary" icon="check" wire:click="aprovar({{ $f->id }})" wire:confirm="Confirmar que recebeste {{ $faturacao->valor($f) }} de {{ $f->conta?->nome }}? O plano fica activo de imediato.">Confirmar</x-ui.button>
                                <x-ui.button size="sm" variant="ghost" wire:click="abrirRejeitar({{ $f->id }})">Rejeitar</x-ui.button>
                            </div>
                        @elseif($f->estado === 'paga')
                            <a href="{{ route('faturas.recibo', $f) }}" class="btn btn--ghost btn--sm">Recibo</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.data-table>
        <x-ui.pagination :paginator="$this->faturas" />
    </x-ui.card>

    @if($rejeitarId)
        <x-ui.dialog title="Rejeitar pagamento" description="O dono da conta recebe um email com o motivo e pode pedir o plano outra vez." width="440" wire:click="fecharRejeitar">
            <x-slot:close><x-ui.icon-button icon="x" label="Fechar" wire:click="fecharRejeitar" /></x-slot:close>
            <form wire:submit="rejeitar" class="pilha pilha--12">
                <x-ui.input label="Motivo" wire:model="motivo" placeholder="Ex.: não encontrámos esta referência no extracto" :error="$errors->first('motivo')" autofocus />
                <div class="linha-accoes">
                    <x-ui.button size="sm" variant="ghost" wire:click="fecharRejeitar">Cancelar</x-ui.button>
                    <x-ui.button type="submit" size="sm" variant="danger">Rejeitar</x-ui.button>
                </div>
            </form>
        </x-ui.dialog>
    @endif
</div>
