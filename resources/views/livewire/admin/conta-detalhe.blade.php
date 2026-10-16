<div class="pilha">
    <x-ui.page-header :title="$conta->nome" :meta="'Conta #'.$conta->id.' · criada a '.$conta->created_at->format('d/m/Y')" back="Contas" :back-href="route('admin.contas')" />

    @if($conta->suspensa())
        <x-ui.alert-banner tone="danger" title="Conta suspensa">{{ $conta->motivo_suspensao }}</x-ui.alert-banner>
    @endif

    <div class="stats-4">
        <x-ui.stat-card label="Plano" :value="$conta->planoEfectivo()->nome.($conta->em_teste ? ' (teste)' : '')" :footnote="$conta->plano_ate ? 'até '.$conta->plano_ate->format('d/m/Y') : 'sem data de fim'" icon="credit-card" />
        <x-ui.stat-card label="Clientes" :value="$conta->clientes_count" icon="users" />
        <x-ui.stat-card label="Serviços" :value="$conta->servicos_count" icon="package" />
        <x-ui.stat-card label="Pagamentos registados" :value="$conta->pagamentos_count" icon="banknote" />
    </div>

    <div class="grelha-2">
        <x-ui.card title="Membros">
            <ul class="lista-simples">
                @foreach($membros as $m)
                    <li wire:key="m-{{ $m->id }}">
                        <div class="lista-simples__corpo">
                            <span class="lista-simples__titulo">{{ $m->name }}</span>
                            <span class="lista-simples__meta">{{ $m->email }} · {{ \App\Enums\Papel::from($m->pivot->papel)->label() }}</span>
                        </div>
                        @unless($m->is(auth()->user()))
                            <form method="POST" action="{{ route('admin.entrar-como', $m) }}">
                                @csrf
                                <x-ui.button type="submit" size="sm" variant="ghost" icon="log-in">Entrar como</x-ui.button>
                            </form>
                        @endunless
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <x-ui.card title="Plano (manual)" subtitle="Para ofertas, acertos ou pagamentos recebidos fora da app. Em branco = sem data de fim.">
            <form wire:submit="guardarPlano" class="pilha pilha--12">
                <x-ui.select label="Plano" wire:model="planoId" :options="$planos->map(fn ($p) => ['value' => (string) $p->id, 'label' => $p->nome])->all()" />
                <x-ui.input label="Até" type="date" wire:model="planoAte" :error="$errors->first('planoAte')" />
                <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar plano</x-ui.button></div>
            </form>
        </x-ui.card>
    </div>

    <x-ui.card title="Faturas" :padding="0">
        @if($faturas->isEmpty())
            <x-ui.empty-state icon="receipt" title="Sem faturas" />
        @else
            <x-ui.data-table :columns="[['header' => 'Fatura'], ['header' => 'Plano'], ['header' => 'Valor', 'align' => 'right'], ['header' => 'Estado'], ['header' => 'Data']]">
                @foreach($faturas as $f)
                    <tr wire:key="f-{{ $f->id }}">
                        <td class="num">{{ $f->referenciaInterna() }}</td>
                        <td>{{ $f->plano->nome }} · {{ \App\Services\Faturacao::CICLOS[$f->ciclo] ?? $f->ciclo }}</td>
                        <td class="u-text-right num">{{ $faturacao->valor($f) }}</td>
                        <td><x-ui.tag :tone="$f->estadoTom()">{{ $f->estadoLabel() }}</x-ui.tag></td>
                        <td class="num">{{ $f->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        @endif
    </x-ui.card>

    <div class="grelha-2">
        <x-ui.card :title="$conta->suspensa() ? 'Reactivar' : 'Suspender'" :subtitle="$conta->suspensa() ? 'Os membros voltam a ter acesso.' : 'Os membros deixam de ver os dados da empresa até reactivares. Nada é apagado.'">
            @if($conta->suspensa())
                <div class="linha-accoes"><x-ui.button size="sm" variant="primary" wire:click="reactivar">Reactivar conta</x-ui.button></div>
            @else
                <form wire:submit="suspender" class="pilha pilha--12">
                    <x-ui.input label="Motivo (os membros vêem-no)" wire:model="motivo" :error="$errors->first('motivo')" />
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm" icon="ban">Suspender conta</x-ui.button></div>
                </form>
            @endif
        </x-ui.card>

        <x-ui.card title="Apagar conta" subtitle="Apaga para sempre os clientes, serviços, pagamentos e configurações desta empresa. Os utilizadores continuam a existir.">
            <form wire:submit="apagar" class="pilha pilha--12">
                <x-ui.input label="Escreve o nome da conta para confirmar" wire:model="confirmarNome" :placeholder="$conta->nome" :error="$errors->first('confirmarNome')" />
                <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="danger" icon="trash-2">Apagar definitivamente</x-ui.button></div>
            </form>
        </x-ui.card>
    </div>
</div>
