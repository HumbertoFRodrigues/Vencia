<?php

namespace App\Models;

use App\Models\Concerns\PertenceAConta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Money taken from the business into the owner's personal panel. */
class Retirada extends Model
{
    use PertenceAConta;

    protected $fillable = ['data', 'valor', 'meio', 'nota', 'user_id'];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The personal entrada it created. Read without the user scope: the business side shows it for any admin. */
    public function movimento(): HasOne
    {
        return $this->hasOne(MovimentoPessoal::class)->withoutGlobalScope('utilizador');
    }
}
