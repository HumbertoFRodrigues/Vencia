<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:12px;align-items:start">
    <x-ui.card title="Verificação em dois passos" subtitle="Além da password, o login pede um código de 6 dígitos que muda a cada 30 segundos na app do teu telemóvel (Google Authenticator, Microsoft Authenticator, Authy…).">
        @if($this->estado === 'desligado')
            <div style="display:flex;flex-direction:column;gap:12px;align-items:flex-start">
                <span class="estado-pill estado-pill--previsto">Desligada</span>
                <p class="pessoal-muted">Recomendado: mesmo que alguém descubra a tua password, não entra sem o teu telemóvel.</p>
                <x-ui.button variant="primary" icon="shield-check" wire:click="iniciar">Activar</x-ui.button>
            </div>
        @elseif($this->estado === 'a_configurar')
            <div class="dois-passos-config">
                <ol class="dois-passos-config__passos">
                    <li>Na app autenticadora, adiciona uma conta e lê este código QR.</li>
                    <li>Escreve o código de 6 dígitos que a app mostrar.</li>
                </ol>
                <div class="dois-passos-config__qr" aria-label="Código QR para a app autenticadora">{!! $this->qr !!}</div>
                <p class="field__hint">Não dá para ler o QR? Escreve esta chave na app: <strong class="num">{{ $this->segredo }}</strong></p>
                <x-ui.input label="Código da app" wire:model="codigo" inputmode="numeric" autocomplete="one-time-code" placeholder="123 456" :error="$errors->first('codigo')" />
                <div style="display:flex;gap:8px">
                    <x-ui.button wire:click="cancelar">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" icon="check" wire:click="confirmar">Confirmar e activar</x-ui.button>
                </div>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:12px">
                <span class="estado-pill estado-pill--confirmado" style="align-self:flex-start">Activa</span>
                <p class="pessoal-muted">Códigos de recuperação por usar: <strong>{{ $this->codigosRestantes }}</strong> de 8. Cada um serve uma vez, se perderes o telemóvel.</p>
                <x-ui.input label="Password (para gerar códigos novos ou desligar)" type="password" wire:model="password" :error="$errors->first('password')" />
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <x-ui.button icon="refresh-cw" wire:click="novosCodigos">Gerar códigos novos</x-ui.button>
                    <x-ui.button variant="ghost" class="action-danger-quiet" wire:click="desactivar" wire:confirm="Desligar a verificação em dois passos?">Desligar</x-ui.button>
                </div>
            </div>
        @endif
    </x-ui.card>

    @if($codigosNovos)
        <x-ui.card title="Códigos de recuperação" subtitle="Guarda-os agora (imprime ou copia para um gestor de passwords). Não voltam a ser mostrados.">
            <ul class="codigos-recuperacao">
                @foreach($codigosNovos as $c)
                    <li class="num">{{ $c }}</li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
</div>
