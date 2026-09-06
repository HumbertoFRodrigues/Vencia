<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembretes_enviados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->string('intervalo');
            $table->date('vencimento_referencia');
            $table->dateTime('enviado_em');
            $table->timestamps();

            $table->unique(['servico_id', 'intervalo', 'vencimento_referencia'], 'lembretes_enviados_dedup_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembretes_enviados');
    }
};
