<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Actividade" />

    <x-ui.card :padding="0">
        <x-ui.data-table :columns="[['header' => 'Quando'], ['header' => 'Quem'], ['header' => 'Acção'], ['header' => 'Detalhe']]" :empty="$registos->isEmpty()">
            <x-slot:emptyState><x-ui.empty-state icon="history" title="Ainda sem actividade" /></x-slot:emptyState>
            @foreach($registos as $r)
                <tr wire:key="a-{{ $r->id }}">
                    <td class="num" style="white-space:nowrap">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->user?->email ?? 'Sistema' }}</td>
                    <td>{{ \App\Livewire\Admin\Actividade::ACCOES[$r->accao] ?? $r->accao }}</td>
                    <td>
                        @if($r->alvo && str_starts_with($r->alvo, 'conta:'))
                            <a href="{{ route('admin.contas.show', (int) substr($r->alvo, 6)) }}" wire:navigate>Conta #{{ substr($r->alvo, 6) }}</a> ·
                        @endif
                        {{ $r->detalhe }}
                    </td>
                </tr>
            @endforeach
        </x-ui.data-table>
        <x-ui.pagination :paginator="$registos" />
    </x-ui.card>
</div>
