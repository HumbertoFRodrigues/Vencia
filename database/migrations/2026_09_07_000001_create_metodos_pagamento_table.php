<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Same shape/spirit as biblioteca_servicos: an admin-managed reference
        // list, never hard-deleted (arquivado instead). `nome` is the exact
        // string stored in pagamentos.metodo / servicos.metodo_habitual — for
        // the 5 well-known methods it stays the legacy lowercase code
        // ('mpesa', 'emola', ...) so existing ledger rows keep matching with
        // zero data migration; for a custom method it's whatever human name
        // the admin types (e.g. "PayPal"), which also happens to read fine as
        // a display label with no separate label column needed.
        Schema::create('metodos_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('logo_path')->nullable();
            $table->boolean('arquivado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_pagamento');
    }
};
