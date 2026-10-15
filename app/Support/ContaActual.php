<?php

namespace App\Support;

use App\Enums\Papel;
use App\Models\Conta;
use Closure;

/**
 * The conta whose business data this request (or this step of a console
 * command) is working on. Every model using PertenceAConta is filtered by it.
 *
 * Set explicitly by DefinirConta (web) and usar() (commands); otherwise it
 * falls back to the logged-in user's current conta, which covers Livewire
 * update requests. Bound as a scoped instance, so nothing leaks between
 * requests or tests.
 */
class ContaActual
{
    private ?Conta $conta = null;

    private bool $explicita = false;

    private ?int $resolvidaPara = null;

    public static function get(): ?Conta
    {
        return app(self::class)->resolver();
    }

    public static function id(): ?int
    {
        return self::get()?->id;
    }

    public static function definir(?Conta $conta): void
    {
        $instancia = app(self::class);
        $instancia->conta = $conta;
        $instancia->explicita = $conta !== null;
        $instancia->resolvidaPara = null;
    }

    /**
     * Runs $fn with $conta as the current one, then restores the previous.
     *
     * @template T
     *
     * @param  Closure(Conta): T  $fn
     * @return T
     */
    public static function usar(Conta $conta, Closure $fn): mixed
    {
        $instancia = app(self::class);
        $antes = [$instancia->conta, $instancia->explicita, $instancia->resolvidaPara];
        self::definir($conta);

        try {
            return $fn($conta);
        } finally {
            [$instancia->conta, $instancia->explicita, $instancia->resolvidaPara] = $antes;
        }
    }

    /** Where this conta's uploaded logos go on the public disk. */
    public static function pastaLogos(): string
    {
        return 'contas/'.(self::id() ?? 0).'/logos';
    }

    /** The logged-in user's role in the current conta (null outside a conta). */
    public static function papel(): ?Papel
    {
        $conta = self::get();
        $user = auth()->user();

        return $conta && $user ? $user->papelNa($conta) : null;
    }

    private function resolver(): ?Conta
    {
        if ($this->explicita) {
            return $this->conta;
        }

        $user = auth()->user();
        if (! $user) {
            return null;
        }

        if ($this->resolvidaPara !== $user->id) {
            $this->conta = $user->contaActual();
            $this->resolvidaPara = $user->id;
        }

        return $this->conta;
    }
}
