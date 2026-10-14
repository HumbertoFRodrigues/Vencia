<?php

namespace App\Services;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\MovimentoPessoal;
use App\Models\Pagamento;
use App\Models\Retirada;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Withdrawals from the business to the owner's personal panel. The business
 * has no expense ledger, so "disponível" is simply everything clients have
 * paid minus everything already withdrawn.
 */
class Retiradas
{
    public function disponivel(): float
    {
        return (float) Pagamento::query()->sum('valor') - (float) Retirada::query()->sum('valor');
    }

    public function totalNoMes(Carbon $mes): float
    {
        return (float) Retirada::query()
            ->whereBetween('data', [$mes->copy()->startOfMonth()->toDateString(), $mes->copy()->endOfMonth()->toDateString()])
            ->sum('valor');
    }

    /**
     * Records the withdrawal and, in the same transaction, the matching
     * entrada in the logged-in user's personal panel ("Retirada da empresa").
     */
    public function retirar(float $valor, Carbon $data, ?string $meio, ?string $nota): Retirada
    {
        return DB::transaction(function () use ($valor, $data, $meio, $nota): Retirada {
            app(FinancasPessoais::class)->garantirCategoriasPadrao();

            $retirada = Retirada::create([
                'data' => $data->toDateString(),
                'valor' => $valor,
                'meio' => $meio,
                'nota' => $nota,
                'user_id' => auth()->id(),
            ]);

            MovimentoPessoal::create([
                'tipo' => TipoMovimento::Entrada,
                'descricao' => 'Retirada da empresa',
                'categoria_pessoal_id' => $this->categoria()->id,
                'valor' => $valor,
                'data' => $data->toDateString(),
                'meio' => $meio,
                'notas' => $nota,
                'retirada_id' => $retirada->id,
            ]);

            return $retirada;
        });
    }

    /** Undo: the personal entrada goes with it (foreign key cascade). */
    public function anular(Retirada $retirada): void
    {
        $retirada->delete();
    }

    /** The "Retirada da empresa" entrada category, recreated if the user archived it or renamed it away. */
    private function categoria(): CategoriaPessoal
    {
        $categoria = CategoriaPessoal::query()->doTipo(TipoMovimento::Entrada)->where('nome', 'Retirada da empresa')->first();

        if ($categoria) {
            return $categoria;
        }

        return CategoriaPessoal::create([
            'nome' => 'Retirada da empresa', 'tipo' => TipoMovimento::Entrada, 'icone' => 'landmark', 'cor' => 'verde',
            'ordem' => (int) CategoriaPessoal::query()->max('ordem') + 1,
        ]);
    }
}
