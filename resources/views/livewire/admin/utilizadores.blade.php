<div class="pilha">
    <x-ui.page-header eyebrow="Administração da plataforma" title="Utilizadores" />

    <div class="filtros">
        <x-ui.search-input width="300" wire:model.live.debounce.300ms="q" placeholder="Nome ou email" :shortcut="null" />
    </div>

    <x-ui.card :padding="0">
        <x-ui.data-table :columns="[['header' => 'Utilizador'], ['header' => 'Contas'], ['header' => 'Estado'], ['header' => 'Registo'], ['header' => '', 'align' => 'right']]" :empty="$this->utilizadores->isEmpty()">
            <x-slot:emptyState><x-ui.empty-state icon="search-x" title="Nenhum utilizador encontrado" /></x-slot:emptyState>
            @foreach($this->utilizadores as $u)
                <tr wire:key="u-{{ $u->id }}">
                    <td>
                        <div style="display:flex;flex-direction:column">
                            <span style="font-weight:var(--weight-medium);color:var(--text-strong)">{{ $u->name }}</span>
                            <span class="lista-simples__meta">{{ $u->email }}</span>
                        </div>
                    </td>
                    <td>
                        @forelse($u->contas as $c)
                            <a href="{{ route('admin.contas.show', $c) }}" wire:navigate>{{ $c->nome }}</a><span class="lista-simples__meta"> ({{ \App\Enums\Papel::from($c->pivot->papel)->label() }})</span>@if(! $loop->last), @endif
                        @empty
                            <span class="lista-simples__meta">—</span>
                        @endforelse
                    </td>
                    <td style="white-space:nowrap">
                        @if($u->super_admin)<x-ui.tag tone="accent">super-admin</x-ui.tag>@endif
                        @if($u->bloqueado())<x-ui.tag tone="danger">bloqueado</x-ui.tag>@endif
                        @unless($u->hasVerifiedEmail())<x-ui.tag tone="warning">email por confirmar</x-ui.tag>@endunless
                    </td>
                    <td class="num">{{ $u->created_at?->format('d/m/Y') }}</td>
                    <td class="u-text-right">
                        <div class="lista-simples__accoes" style="justify-content:flex-end">
                            @unless($u->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.entrar-como', $u) }}">@csrf<x-ui.button type="submit" size="sm" variant="ghost" icon="log-in">Entrar como</x-ui.button></form>
                            @endunless
                            <div class="dropdown">
                                <x-ui.icon-button icon="ellipsis" label="Mais acções" size="sm" data-dropdown-toggle data-dropdown-group="u-{{ $u->id }}" aria-haspopup="true" aria-expanded="false" />
                                <div class="dropdown-panel" data-dropdown-panel data-dropdown-group="u-{{ $u->id }}" hidden>
                                    <button type="button" class="dropdown-menu-item" wire:click="editar({{ $u->id }})">Editar nome e email</button>
                                    @unless($u->hasVerifiedEmail())
                                        <button type="button" class="dropdown-menu-item" wire:click="reenviarConfirmacao({{ $u->id }})">Reenviar confirmação</button>
                                        <button type="button" class="dropdown-menu-item" wire:click="confirmarEmail({{ $u->id }})">Marcar email como confirmado</button>
                                    @endunless
                                    <button type="button" class="dropdown-menu-item" wire:click="enviarReposicao({{ $u->id }})">Enviar link de reposição da password</button>
                                    @unless($u->is(auth()->user()))
                                        <button type="button" class="dropdown-menu-item" wire:click="bloquear({{ $u->id }})">{{ $u->bloqueado() ? 'Desbloquear' : 'Bloquear' }}</button>
                                        <button type="button" class="dropdown-menu-item" wire:click="alternarSuperAdmin({{ $u->id }})" wire:confirm="{{ $u->super_admin ? 'Retirar o acesso de super-admin a '.$u->email.'?' : 'Dar acesso total à administração a '.$u->email.'?' }}">{{ $u->super_admin ? 'Retirar super-admin' : 'Tornar super-admin' }}</button>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.data-table>
        <x-ui.pagination :paginator="$this->utilizadores" />
    </x-ui.card>

    @if($editandoId)
        <x-ui.dialog title="Editar utilizador" description="Um email corrigido aqui fica logo confirmado." width="440" wire:click="fecharEdicao">
            <x-slot:close><x-ui.icon-button icon="x" label="Fechar" wire:click="fecharEdicao" /></x-slot:close>
            <form wire:submit="guardarEdicao" class="pilha pilha--12">
                <x-ui.input label="Nome" wire:model="editNome" :error="$errors->first('editNome')" />
                <x-ui.input label="Email" type="email" wire:model="editEmail" :error="$errors->first('editEmail')" />
                <div class="linha-accoes">
                    <x-ui.button size="sm" variant="ghost" wire:click="fecharEdicao">Cancelar</x-ui.button>
                    <x-ui.button type="submit" size="sm" variant="primary">Guardar</x-ui.button>
                </div>
            </form>
        </x-ui.dialog>
    @endif
</div>
