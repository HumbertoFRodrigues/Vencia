<?php

namespace Tests;

use App\Models\Conta;
use App\Models\Plano;
use App\Support\ContaActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Every test with a database starts inside one conta (Pro, so plan limits never get in the way). */
    protected ?Conta $conta = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (in_array(RefreshDatabase::class, class_uses_recursive($this), true)) {
            $this->conta = Conta::create([
                'nome' => 'Conta de teste',
                'plano_id' => Plano::query()->where('slug', 'pro')->value('id'),
            ]);
            ContaActual::definir($this->conta);
        }
    }

    /** A second, separate business for isolation tests. */
    protected function outraConta(string $nome = 'Outra conta'): Conta
    {
        return Conta::create(['nome' => $nome, 'plano_id' => Plano::gratis()->id]);
    }
}
