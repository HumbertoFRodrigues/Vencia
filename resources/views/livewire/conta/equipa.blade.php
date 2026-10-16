@php
    $opcoesPapel = collect($papeis)->map(fn ($p) => ['value' => $p->value, 'label' => $p->label()])->all();
    $restantes = $this->restantes;
@endphp

<div class="pilha">
    <x-ui.page-header eyebrow="{{ \App\Support\ContaActual::get()->nome }}" title="Equipa" />

    <div class="grelha-2">
        <x-ui.card title="Membros" subtitle="Cada pessoa tem o seu próprio painel pessoal, que só ela vê.">
            <ul class="lista-simples">
                @foreach($this->membros as $m)
                    @php($papelM = \App\Enums\Papel::from($m->pivot->papel))
                    <li wire:key="membro-{{ $m->id }}">
                        <div class="lista-simples__corpo">
                            <span class="lista-simples__titulo">{{ $m->name }}@if($m->is($eu)) <span class="lista-simples__meta">(tu)</span>@endif</span>
                            <span class="lista-simples__meta">{{ $m->email }}</span>
                        </div>
                        <div class="lista-simples__accoes">
                            <div class="field__select-wrap">
                                <select class="field__select" aria-label="Papel de {{ $m->name }}" wire:change="mudarPapel({{ $m->id }}, $event.target.value)">
                                    @foreach($papeis as $p)
                                        <option value="{{ $p->value }}" @selected($p === $papelM)>{{ $p->label() }}</option>
                                    @endforeach
                                </select>
                                <x-ui.icon name="chevron-down" size="15" color="var(--text-faint)" class="field__select-chevron" />
                            </div>
                            <x-ui.icon-button size="sm" icon="trash-2" :label="'Remover '.$m->name" wire:click="remover({{ $m->id }})" wire:confirm="Remover {{ $m->name }} da equipa? Deixa de ver os dados da empresa (o pessoal dele não é afectado)." />
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <div class="pilha">
            <x-ui.card title="Convidar" :subtitle="$restantes === null ? 'Envia um convite por email. A pessoa cria a conta (ou entra) e fica na equipa.' : ($restantes > 0 ? 'Podes convidar mais '.$restantes.' '.($restantes === 1 ? 'pessoa' : 'pessoas').' no teu plano.' : 'Chegaste ao limite de membros do teu plano.')">
                @if($restantes === 0)
                    <p class="pessoal-muted" style="margin:0">Para juntar mais pessoas, <a href="{{ route('conta.plano') }}" wire:navigate>passa para o Pro</a>.</p>
                @else
                    <form wire:submit="convidar" class="pilha pilha--12">
                        <x-ui.input label="Email" type="email" wire:model="email" placeholder="colega@exemplo.co.mz" :error="$errors->first('email')" />
                        <x-ui.select label="Papel" wire:model.live="papel" :options="$opcoesPapel" :hint="\App\Enums\Papel::tryFrom($papel)?->descricao()" />
                        <div class="linha-accoes">
                            <x-ui.button type="submit" variant="primary" size="sm" icon="send">Enviar convite</x-ui.button>
                        </div>
                    </form>
                @endif
            </x-ui.card>

            @if($this->convites->isNotEmpty())
                <x-ui.card title="Convites por aceitar">
                    <ul class="lista-simples">
                        @foreach($this->convites as $c)
                            <li wire:key="convite-{{ $c->id }}">
                                <div class="lista-simples__corpo">
                                    <span class="lista-simples__titulo">{{ $c->email }}</span>
                                    <span class="lista-simples__meta">{{ $c->papel->label() }} · expira a {{ $c->expira_em->format('d/m/Y') }}</span>
                                </div>
                                <div class="lista-simples__accoes">
                                    <x-ui.button size="sm" variant="ghost" wire:click="reenviar({{ $c->id }})">Reenviar</x-ui.button>
                                    <x-ui.icon-button size="sm" icon="x" label="Anular convite" wire:click="anularConvite({{ $c->id }})" />
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif
        </div>
    </div>
</div>
