<div class="pilha">
    <x-ui.page-header eyebrow="Só tu vês esta página" title="O meu perfil" />

    <div class="grelha-2">
        <div class="pilha">
            <x-ui.card title="Os teus dados">
                <form wire:submit="guardarDados" class="pilha pilha--12">
                    <x-ui.input label="Nome" wire:model="nome" autocomplete="name" :error="$errors->first('nome')" />
                    <x-ui.input label="Email" type="email" wire:model="email" autocomplete="email" :error="$errors->first('email')" hint="Se mudares o email, vais ter de o confirmar outra vez." />
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button></div>
                </form>
            </x-ui.card>

            <x-ui.card title="Palavra-passe">
                <form wire:submit="mudarPassword" class="pilha pilha--12">
                    <x-ui.input label="Palavra-passe actual" type="password" wire:model="passwordActual" autocomplete="current-password" :error="$errors->first('passwordActual')" hint="Também é pedida para mudar o email." />
                    <x-ui.input label="Nova palavra-passe" type="password" wire:model="passwordNova" autocomplete="new-password" :error="$errors->first('passwordNova')" hint="Pelo menos 8 caracteres." />
                    <x-ui.input label="Repetir a nova palavra-passe" type="password" wire:model="passwordNova_confirmation" autocomplete="new-password" />
                    <div class="linha-accoes"><x-ui.button type="submit" size="sm" variant="primary">Mudar palavra-passe</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>

        <div class="pilha">
            <x-ui.card title="As tuas empresas" subtitle="O teu painel pessoal é o mesmo em todas.">
                <ul class="lista-simples">
                    @forelse($this->contas as $c)
                        @php
                            $papel = $papeis::from($c->pivot->papel);
                        @endphp
                        <li wire:key="conta-{{ $c->id }}">
                            <div class="lista-simples__corpo">
                                <span class="lista-simples__titulo">{{ $c->nome }} @if($c->id === $actual)<x-ui.tag tone="accent">actual</x-ui.tag>@endif</span>
                                <span class="lista-simples__meta">{{ $papel->label() }} · plano {{ $c->planoEfectivo()->nome }}</span>
                            </div>
                            <div class="lista-simples__accoes">
                                @if($c->id !== $actual)
                                    <form method="POST" action="{{ route('conta.mudar') }}">
                                        @csrf
                                        <input type="hidden" name="conta" value="{{ $c->id }}">
                                        <x-ui.button type="submit" size="sm" variant="ghost">Entrar</x-ui.button>
                                    </form>
                                @endif
                                <x-ui.button size="sm" variant="ghost" wire:click="sair({{ $c->id }})" wire:confirm="Sair da equipa de {{ $c->nome }}? Deixas de ver os dados desta empresa. O teu pessoal não é afectado.">Sair</x-ui.button>
                            </div>
                        </li>
                    @empty
                        <li><span class="lista-simples__meta">Ainda não pertences a nenhuma empresa.</span></li>
                    @endforelse
                </ul>
                <div class="linha-accoes" style="margin-top:12px">
                    <a href="{{ route('conta.criar') }}" class="btn btn--secondary btn--sm"><x-ui.icon name="plus" size="15" /> Criar outra empresa</a>
                </div>
            </x-ui.card>

            <livewire:configuracoes.seguranca />
        </div>
    </div>
</div>
