<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentos_pessoais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('descricao');
            $table->foreignId('categoria_pessoal_id')->constrained('categorias_pessoais')->restrictOnDelete();
            $table->decimal('valor', 12, 2);
            $table->date('data');
            $table->string('meio')->nullable();
            // A confirmed (or skipped) occurrence of a fixo for one month.
            // `competencia` is that month's first day; together with the fixo
            // it is the "never confirm the same fixo twice in a month" key.
            $table->foreignId('fixo_pessoal_id')->nullable()->constrained('fixos_pessoais')->nullOnDelete();
            $table->date('competencia')->nullable();
            $table->boolean('ignorado')->default(false);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['fixo_pessoal_id', 'competencia']);
            $table->index(['user_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentos_pessoais');
    }
};
