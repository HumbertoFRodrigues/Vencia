<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every business table gets its conta. An existing installation (the one
 * that ran before Vencia had accounts) becomes one conta: its first user is
 * the dono and the platform's super-admin, every other user a gestor, and
 * it keeps the Pro plan with no end date. Receipt numbers stay as they were.
 */
return new class extends Migration
{
    private const TABELAS = ['clientes', 'servicos', 'pagamentos', 'historicos', 'biblioteca_servicos', 'metodos_pagamento', 'retiradas', 'configuracoes'];

    public function up(): void
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->foreignId('conta_id')->nullable()->after('id')->constrained('contas')->cascadeOnDelete();
            });
        }

        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropUnique(['chave']);
            $table->unique(['conta_id', 'chave']);
        });

        Schema::table('metodos_pagamento', function (Blueprint $table) {
            $table->dropUnique(['nome']);
            $table->unique(['conta_id', 'nome']);
        });

        Schema::table('pagamentos', function (Blueprint $table) {
            $table->unsignedInteger('numero')->nullable()->after('conta_id');
            $table->unique(['conta_id', 'numero']);
        });

        $this->converterInstalacaoExistente();
    }

    private function converterInstalacaoExistente(): void
    {
        $users = DB::table('users')->orderBy('id')->get();
        $temDados = $users->isNotEmpty() || DB::table('clientes')->exists() || DB::table('configuracoes')->exists();

        if (! $temDados) {
            return;
        }

        $agora = Carbon::now();
        $empresa = json_decode((string) DB::table('configuracoes')->where('chave', 'empresa')->value('valor'), true) ?: [];

        $contaId = DB::table('contas')->insertGetId([
            'nome' => $empresa['nome'] ?? config('app.name', 'Vencia'),
            'estado' => 'activa',
            'plano_id' => DB::table('planos')->where('slug', 'pro')->value('id'),
            'plano_ate' => null,
            'proximo_recibo' => (int) DB::table('pagamentos')->max('id') + 1,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        foreach (self::TABELAS as $tabela) {
            DB::table($tabela)->update(['conta_id' => $contaId]);
        }

        DB::table('pagamentos')->update(['numero' => DB::raw('id')]);

        $admin = $users->firstWhere('email', config('app.admin_email')) ?? $users->first();

        foreach ($users as $user) {
            DB::table('conta_user')->insert([
                'conta_id' => $contaId,
                'user_id' => $user->id,
                'papel' => $user->id === $admin->id ? 'dono' : 'gestor',
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }

        DB::table('users')->update(['conta_actual_id' => $contaId]);

        // Email confirmation is new: people who already used the app are not asked to confirm.
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => $agora]);

        if ($admin) {
            DB::table('users')->where('id', $admin->id)->update(['super_admin' => true]);
        }

        // The SMTP saved so far keeps sending the platform's own emails too (password resets, sign-up).
        $smtp = DB::table('configuracoes')->where('chave', 'smtp')->value('valor');
        if ($smtp !== null) {
            DB::table('configuracoes')->insert(['conta_id' => null, 'chave' => 'smtp', 'valor' => $smtp, 'created_at' => $agora, 'updated_at' => $agora]);
        }
    }

    public function down(): void
    {
        // MySQL backs each conta_id foreign key with the composite unique indexes below: drop the keys first.
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropForeign(['conta_id']);
            });
        }

        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropUnique(['conta_id', 'numero']);
            $table->dropColumn('numero');
        });

        DB::table('configuracoes')->whereNull('conta_id')->delete();

        Schema::table('metodos_pagamento', function (Blueprint $table) {
            $table->dropUnique(['conta_id', 'nome']);
            $table->unique('nome');
        });

        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropUnique(['conta_id', 'chave']);
            $table->unique('chave');
        });

        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropColumn('conta_id');
            });
        }
    }
};
