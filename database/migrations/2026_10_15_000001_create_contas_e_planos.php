<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vencia as a service: contas (one business each, with members), the plans
 * they are on, the invoices paid for them, team invites and the super-admin
 * activity log. The plan lives on the conta itself (plano_id + plano_ate);
 * faturas are the history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nome');
            $table->string('descricao', 500)->nullable();
            $table->decimal('preco_mensal', 12, 2)->default(0);
            $table->decimal('preco_anual', 12, 2)->default(0);
            $table->json('limites');
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('contas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('estado')->default('activa');
            $table->string('motivo_suspensao', 500)->nullable();
            $table->foreignId('plano_id')->nullable()->constrained('planos')->nullOnDelete();
            $table->date('plano_ate')->nullable();
            $table->boolean('em_teste')->default(false);
            $table->unsignedInteger('proximo_recibo')->default(1);
            $table->json('avisos_plano')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('papel');
            $table->timestamps();

            $table->unique(['conta_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('super_admin')->default(false)->after('password');
            $table->foreignId('conta_actual_id')->nullable()->after('super_admin')->constrained('contas')->nullOnDelete();
            $table->timestamp('bloqueado_em')->nullable()->after('conta_actual_id');
        });

        Schema::create('convites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->string('email');
            $table->string('papel');
            $table->string('token', 64)->unique();
            $table->foreignId('convidado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expira_em');
            $table->timestamp('aceite_em')->nullable();
            $table->timestamps();
        });

        Schema::create('faturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->restrictOnDelete();
            $table->string('ciclo');
            $table->decimal('valor', 12, 2);
            $table->string('estado')->default('pendente');
            $table->string('metodo')->nullable();
            $table->string('referencia')->nullable();
            $table->string('comprovativo_path')->nullable();
            $table->string('gateway')->default('manual');
            $table->string('gateway_ref')->nullable();
            $table->string('nota', 500)->nullable();
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fim')->nullable();
            $table->foreignId('pedida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprovada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enviada_em')->nullable();
            $table->timestamp('paga_em')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });

        Schema::create('actividade_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accao');
            $table->string('alvo')->nullable();
            $table->string('detalhe', 1000)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        $agora = Carbon::now();
        DB::table('planos')->insert([
            [
                'slug' => 'gratis', 'nome' => 'Grátis', 'ordem' => 1, 'activo' => true,
                'descricao' => 'Painel pessoal completo e uma pequena carteira de clientes.',
                'preco_mensal' => 0, 'preco_anual' => 0,
                'limites' => json_encode(['clientes' => 10, 'servicos' => 15, 'membros' => 1, 'exportar' => false, 'smtp_proprio' => false]),
                'created_at' => $agora, 'updated_at' => $agora,
            ],
            [
                'slug' => 'pro', 'nome' => 'Pro', 'ordem' => 2, 'activo' => true,
                'descricao' => 'Clientes e serviços sem limite, equipa, exportações e o teu próprio servidor de email.',
                'preco_mensal' => 500, 'preco_anual' => 5000,
                'limites' => json_encode(['clientes' => null, 'servicos' => null, 'membros' => 10, 'exportar' => true, 'smtp_proprio' => true]),
                'created_at' => $agora, 'updated_at' => $agora,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conta_actual_id');
            $table->dropColumn(['super_admin', 'bloqueado_em']);
        });

        Schema::dropIfExists('actividade_admin');
        Schema::dropIfExists('faturas');
        Schema::dropIfExists('convites');
        Schema::dropIfExists('conta_user');
        Schema::dropIfExists('contas');
        Schema::dropIfExists('planos');
    }
};
