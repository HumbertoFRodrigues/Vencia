<?php

namespace App\Livewire\Concerns;

use App\Models\Plano;
use App\Services\Limites;

/** Stops an action the conta's plan doesn't allow, with a toast pointing to Plano. */
trait RespeitaPlano
{
    /** True (and a toast is shown) when $chave is a reached limit or a feature the plan lacks. */
    protected function bloqueadoPeloPlano(string $chave, int $mais = 1): bool
    {
        $limites = app(Limites::class);
        $bloqueado = array_key_exists($chave, Plano::FUNCOES)
            ? ! $limites->permite($chave)
            : ! $limites->pode($chave, $mais);

        if ($bloqueado) {
            $aviso = $limites->aviso($chave);
            $this->dispatch('toast', title: $aviso['title'], body: $aviso['body'], tone: $aviso['tone']);
        }

        return $bloqueado;
    }
}
