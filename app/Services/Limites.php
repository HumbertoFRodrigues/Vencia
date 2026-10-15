<?php

namespace App\Services;

use App\Enums\ServicoStatus;
use App\Models\Cliente;
use App\Models\Conta;
use App\Models\Convite;
use App\Models\Plano;
use App\Models\Servico;
use App\Support\ContaActual;

/**
 * What the current conta's plan allows. Limits only stop new things being
 * added: nothing is ever hidden or deleted when a conta goes back to Grátis.
 */
class Limites
{
    public function conta(): ?Conta
    {
        return ContaActual::get();
    }

    public function plano(): Plano
    {
        return $this->conta()?->planoEfectivo() ?? Plano::gratis();
    }

    public function uso(string $chave): int
    {
        return match ($chave) {
            'clientes' => Cliente::query()->count(),
            'servicos' => Servico::query()->whereNotIn('status', [ServicoStatus::Cancelado])->count(),
            'membros' => $this->conta()
                ? $this->conta()->membros()->count() + Convite::query()->where('conta_id', $this->conta()->id)->pendentes()->count()
                : 0,
            default => 0,
        };
    }

    /** How many more can be added (null = no limit). */
    public function restante(string $chave): ?int
    {
        $limite = $this->plano()->limite($chave);

        return $limite === null ? null : max(0, $limite - $this->uso($chave));
    }

    public function pode(string $chave, int $mais = 1): bool
    {
        $restante = $this->restante($chave);

        return $restante === null || $restante >= $mais;
    }

    public function permite(string $funcao): bool
    {
        return $this->plano()->permite($funcao);
    }

    /** The toast shown when a limit stops an action. */
    public function aviso(string $chave): array
    {
        $nome = mb_strtolower(Plano::LIMITES[$chave] ?? Plano::FUNCOES[$chave] ?? $chave);
        $limite = $this->plano()->limite($chave);

        return [
            'title' => 'Limite do plano '.$this->plano()->nome,
            'body' => $limite !== null
                ? "O teu plano inclui até {$limite} {$nome}. Passa para o Pro em Plano para continuar."
                : "{$this->plano()->nome} não inclui: {$nome}. Passa para o Pro em Plano.",
            'tone' => 'warning',
        ];
    }
}
