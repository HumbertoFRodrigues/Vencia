<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('biblioteca_servico_id')->nullable()->constrained('biblioteca_servicos')->nullOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('plano')->nullable();
            $table->string('categoria');
            $table->decimal('valor', 10, 2);
            $table->string('periodo');
            $table->string('periodicidade');
            $table->unsignedInteger('duracao_dias')->nullable();
            $table->date('inicio');
            $table->date('vencimento')->nullable();
            $table->string('status')->default('activo');
            $table->date('proximo_aviso')->nullable();
            $table->string('metodo_habitual')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
