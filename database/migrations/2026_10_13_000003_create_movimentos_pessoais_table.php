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
            // `competencia` is the month (its first day) the money belongs to — every
            // total groups by it, so October's rent paid on 2 November still counts
            // in October. For a day-to-day movimento it is simply the month of `data`.
            // With fixo_pessoal_id it is also the "one confirmation per fixo per month" key.
            $table->foreignId('fixo_pessoal_id')->nullable()->constrained('fixos_pessoais')->cascadeOnDelete();
            $table->date('competencia');
            $table->boolean('ignorado')->default(false);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['fixo_pessoal_id', 'competencia']);
            $table->index(['user_id', 'competencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentos_pessoais');
    }
};
