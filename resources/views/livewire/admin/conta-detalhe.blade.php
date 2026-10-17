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
        <x-ui.card title="Membros" :subtitle="$membros->count().($membros->count() === 1 ? ' pessoa' : ' pessoas').' · uma conta tem sempre pelo menos um dono'">
            <ul class="lista-simples">
                @foreach($membros as $m)
                    @php
                        $papelM = \App\Enums\Papel::from($m->pivot->papel);
                    @endphp
                    <li wire:key="m-{{ $m->id }}">
                        <div class="lista-simples__corpo">
                            <span class="lista-simples__titulo">{{ $m->name }}</span>
                            <span class="lista-simples__meta">{{ $m->email }}</span>
                        </div>
                        <div class="lista-simples__accoes">
                            <div class="field__select-wrap">
                                <select class="field__select" aria-label="Papel de {{ $m->name }}" wire:change="mudarPapelMembro({{ $m->id }}, $event.target.value)">
                                    @foreach(\App\Enums\Papel::cases() as $p)
                                        <option value="{{ $p->value }}" @selected($p === $papelM)>{{ $p->label() }}</option>
                                    @endforeach
                                </select>
                                <x-ui.icon name="chevron-down" size="15" color="var(--text-faint)" class="field__select-chevron" />
                            </div>
                            @unless($m->is(auth()->user()) || $m->super_admin)
                                <form method="POST" action="{{ route('admin.entrar-como', $m) }}">
                                    @csrf
                                    <x-ui.icon-button type="submit" size="sm" icon="log-in" :label="'Entrar como '.$m->name" />
                                </form>
                            @endunless
                            <x-ui.icon-button size="sm" icon="trash-2" :label="'Remover '.$m->name.' da conta'" wire:click="removerMembro({{ $m->id }})" wire:confirm="Remover {{ $m->name }} desta conta? O utilizador continua a existir." />
                        </div>
                    </li>
                @endforeach
            </ul>
            <form wire:submit="adicionarMembro" class="grelha-2" style="margin-top:14px;align-items:end">
                <x-ui.input label="Acrescentar utilizador existente" type="email" wire:model="novoMembroEmail" placeholder="email@exemplo.mz" :error="$errors->first('novoMembroEmail')" />
                <div style="display:flex;gap:8px;align-items:end">
                    <div style="flex:1"><x-ui.select label="Papel" wire:model="novoMembroPapel" :options="collect(\App\Enums\Papel::cases())->map(fn ($p) => ['value' => $p->value, 'label' => $p->label()])->all()" /></div>
                    <x-ui.button type="submit" size="sm" icon="user-plus">Acrescentar</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="pilha">
            <x-ui.card title="Nome da conta">
                <form wire:submit="guardarNome" class="pilha pilha--12">
                    <x-ui.input wire:model="nome" :error="$errors->first('nome')" aria-label="Nome da conta" />
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm">Guardar nome</x-ui.button></div>
                </form>
            </x-ui.card>

            <x-ui.card title="Plano (manual)" subtitle="Para ofertas, acertos ou pagamentos recebidos fora da app. Em branco = sem data de fim.">
                <div class="pilha pilha--12">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                        <span class="lista-simples__meta">Oferecer Pro:</span>
                        @foreach([7, 14, 30] as $d)
                            <x-ui.button size="sm" variant="secondary" wire:click="oferecerDias({{ $d }})">+{{ $d }} dias</x-ui.button>
                        @endforeach
                    </div>
                    <form wire:submit="guardarPlano" class="pilha pilha--12">
                        <x-ui.select label="Plano" wire:model="planoId" :options="$planos->map(fn ($p) => ['value' => (string) $p->id, 'label' => $p->nome])->all()" />
                        <x-ui.input label="Até" type="date" wire:model="planoAte" :error="$errors->first('planoAte')" />
                        <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar plano</x-ui.button></div>
                    </form>
                </div>
            </x-ui.card>
        </div>
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
