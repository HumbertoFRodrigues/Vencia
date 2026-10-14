<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Two-step login: an authenticator-app secret (encrypted), single-use recovery codes, and the last accepted time step (no code reuse). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('dois_passos_segredo')->nullable();
            $table->text('dois_passos_recuperacao')->nullable();
            $table->timestamp('dois_passos_confirmado_em')->nullable();
            $table->unsignedBigInteger('dois_passos_ultimo_passo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dois_passos_segredo', 'dois_passos_recuperacao', 'dois_passos_confirmado_em', 'dois_passos_ultimo_passo']);
        });
    }
};
