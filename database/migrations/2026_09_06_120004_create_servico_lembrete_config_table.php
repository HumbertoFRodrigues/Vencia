<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each flag is nullable: null means "inherit the global configuracoes
        // default for this interval", true/false means this servico explicitly
        // overrides that interval. This is what makes the two-tier reminder
        // config (global default + per-servico override) actually work.
        Schema::create('servico_lembrete_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->unique()->constrained('servicos')->cascadeOnDelete();
            $table->boolean('d30')->nullable();
            $table->boolean('d15')->nullable();
            $table->boolean('d7')->nullable();
            $table->boolean('d3')->nullable();
            $table->boolean('d1')->nullable();
            $table->boolean('no_dia')->nullable();
            $table->boolean('apos_vencimento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servico_lembrete_config');
    }
};
