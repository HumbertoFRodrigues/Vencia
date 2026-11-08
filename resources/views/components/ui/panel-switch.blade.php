@props(['active' => 'empresa'])
@php
    // The three areas of Vencia, each explained in one line so nobody has to guess what they are.
    $admin = (bool) auth()->user()?->super_admin;
    $conta = \App\Support\ContaActual::get();
    $nContas = auth()->user()?->contas()->count() ?? 0;

    $areas = array_filter([
        'empresa' => ['nome' => 'Empresa', 'sub' => $conta?->nome ?? 'A tua empresa', 'icone' => 'briefcase', 'rota' => 'dashboard',
            'descricao' => 'Clientes, serviços, cobranças e recibos'.($conta ? ' de '.$conta->nome : '').'.'],
        'pessoal' => ['nome' => 'Pessoal', 'sub' => 'Só tu vês', 'icone' => 'wallet', 'rota' => 'pessoal.resumo',
            'descricao' => 'O teu dinheiro: gastos, orçamentos e metas. Privado, nem a equipa vê.'],
        'admin' => $admin ? ['nome' => 'Administração', 'sub' => 'Plataforma Vencia', 'icone' => 'shield-check', 'rota' => 'admin.visao',
            'descricao' => 'Gerir todas as contas, planos, faturação e o envio de email.'] : null,
    ]);
    $actual = $areas[$active] ?? $areas['empresa'];
@endphp
<div class="area-switch dropdown">
    <button type="button" class="area-switch__botao" data-dropdown-toggle data-dropdown-group="area-switch" aria-haspopup="true" aria-expanded="false" title="Mudar de área">
        <span class="area-switch__icone area-switch__icone--{{ $active }}"><x-ui.icon :name="$actual['icone']" size="16" /></span>
        <span class="area-switch__texto">
            <span class="area-switch__nome">{{ $actual['nome'] }}</span>
            <span class="area-switch__sub">{{ $actual['sub'] }}</span>
        </span>
        <x-ui.icon name="chevrons-up-down" size="14" class="area-switch__seta" />
    </button>

    <div class="dropdown-panel area-switch__painel" data-dropdown-panel data-dropdown-group="area-switch" hidden>
        <span class="area-switch__grupo">Mudar de área</span>
        @foreach($areas as $id => $a)
            <a href="{{ route($a['rota']) }}" wire:navigate class="area-switch__opcao {{ $id === $active ? 'area-switch__opcao--actual' : '' }}" @if($id === $active) aria-current="true" @endif>
                <span class="area-switch__icone area-switch__icone--{{ $id }}"><x-ui.icon :name="$a['icone']" size="16" /></span>
                <span class="area-switch__texto">
                    <span class="area-switch__nome">{{ $a['nome'] }}</span>
                    <span class="area-switch__descricao">{{ $a['descricao'] }}</span>
                </span>
                @if($id === $active)<x-ui.icon name="check" size="15" class="area-switch__visto" />@endif
            </a>
        @endforeach
        @if($nContas > 1)
            <div class="area-switch__divisor"></div>
            <a href="{{ route('conta.escolher') }}" class="dropdown-menu-item">
                <x-ui.icon name="building-2" size="16" /> Mudar de empresa
            </a>
        @endif
    </div>
</div>
