<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_pessoais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome');
            $table->string('tipo');
            $table->string('icone')->default('package');
            $table->string('cor')->default('cinza');
            $table->decimal('limite_mensal', 12, 2)->nullable();
            $table->boolean('arquivado')->default(false);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'tipo', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_pessoais');
    }
};
