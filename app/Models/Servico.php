<?php

namespace App\Models;

use App\Enums\MetodoPagamento;
use App\Enums\Periodicidade;
use App\Enums\Periodo;
use App\Enums\ServicoCategoria;
use App\Enums\ServicoStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Servico extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'biblioteca_servico_id', 'nome', 'descricao', 'plano',
        'categoria', 'valor', 'periodo', 'periodicidade', 'duracao_dias',
        'inicio', 'vencimento', 'status', 'proximo_aviso', 'metodo_habitual', 'logo_path',
    ];

    protected $casts = [
        'categoria' => ServicoCategoria::class,
        'valor' => 'decimal:2',
        'periodo' => Periodo::class,
        'periodicidade' => Periodicidade::class,
        'duracao_dias' => 'integer',
        'inicio' => 'date',
        'vencimento' => 'date',
        'status' => ServicoStatus::class,
        'proximo_aviso' => 'date',
        'metodo_habitual' => MetodoPagamento::class,
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function bibliotecaServico(): BelongsTo
    {
        return $this->belongsTo(BibliotecaServico::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(Historico::class);
    }

    public function suspensoes(): HasMany
    {
        return $this->hasMany(Suspensao::class);
    }

    public function lembretesEnviados(): HasMany
    {
        return $this->hasMany(LembreteEnviado::class);
    }

    public function lembreteConfig(): HasOne
    {
        return $this->hasOne(ServicoLembreteConfig::class);
    }

    /**
     * Signed number of days from today until vencimento (negative once overdue).
     * Never persisted. Null when vencimento is null (e.g. periodicidade "unica"
     * without a fixed due date).
     */
    protected function dias(): Attribute
    {
        return Attribute::get(function (): ?int {
            if ($this->vencimento === null) {
                return null;
            }

            /** @var Carbon $vencimento */
            $vencimento = $this->vencimento;

            return (int) Carbon::today()->diffInDays($vencimento, false);
        });
    }
}
