<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Money taken out of the business into the owner's personal finances.
 * Each retirada creates the matching entrada in movimentos_pessoais
 * (linked by retirada_id, removed with it), so both panels always agree.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retiradas', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->decimal('valor', 12, 2);
            $table->string('meio')->nullable();
            $table->string('nota', 500)->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('data');
        });

        Schema::table('movimentos_pessoais', function (Blueprint $table) {
            $table->foreignId('retirada_id')->nullable()->after('fixo_pessoal_id')->constrained('retiradas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimentos_pessoais', function (Blueprint $table) {
            $table->dropConstrainedForeignId('retirada_id');
        });

        Schema::dropIfExists('retiradas');
    }
};
