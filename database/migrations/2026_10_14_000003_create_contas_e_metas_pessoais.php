<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal balances per account (M-Pesa, banco, dinheiro…): the balance is
 * computed from the movimentos' `meio`; only an opening balance is stored,
 * set once through "Acertar saldo". Savings goals: progress is the sum of
 * the saídas linked to the goal (contributions).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_pessoais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('meio');
            $table->decimal('saldo_inicial', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'meio']);
        });

        Schema::create('metas_pessoais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome', 120);
            $table->decimal('valor_alvo', 14, 2);
            $table->date('data_alvo')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
        });

        Schema::table('movimentos_pessoais', function (Blueprint $table) {
            $table->foreignId('meta_pessoal_id')->nullable()->after('retirada_id')->constrained('metas_pessoais')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimentos_pessoais', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meta_pessoal_id');
        });
        Schema::dropIfExists('metas_pessoais');
        Schema::dropIfExists('contas_pessoais');
    }
};
