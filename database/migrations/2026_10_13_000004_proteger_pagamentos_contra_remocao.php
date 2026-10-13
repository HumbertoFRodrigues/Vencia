<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The payment ledger must never disappear as a side effect: deleting a
 * cliente or servico that still has pagamentos is now refused by the
 * database instead of silently cascading the money history away.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['servico_id']);
            $table->foreign('cliente_id')->references('id')->on('clientes')->restrictOnDelete();
            $table->foreign('servico_id')->references('id')->on('servicos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['servico_id']);
            $table->foreign('cliente_id')->references('id')->on('clientes')->cascadeOnDelete();
            $table->foreign('servico_id')->references('id')->on('servicos')->cascadeOnDelete();
        });
    }
};
