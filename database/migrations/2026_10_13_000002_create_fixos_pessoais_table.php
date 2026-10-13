<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixos_pessoais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('descricao');
            $table->foreignId('categoria_pessoal_id')->constrained('categorias_pessoais')->restrictOnDelete();
            $table->decimal('valor', 12, 2);
            $table->unsignedTinyInteger('dia_mes');
            $table->string('meio')->nullable();
            $table->date('inicio');
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixos_pessoais');
    }
};
