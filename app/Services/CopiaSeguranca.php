<?php

namespace App\Services;

use App\Models\Configuracao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Daily database backup written in plain PHP (shared hosting rarely allows
 * running mysqldump): a gzipped .sql that phpMyAdmin imports as-is. The
 * last few copies are kept on the server and, when SMTP is configured, the
 * new one is emailed to the empresa address so a copy also lives elsewhere.
 */
class CopiaSeguranca
{
    public const MANTER = 14;

    /** Email attachments above this are skipped (most mail servers cap at 20-25 MB). */
    private const MAX_EMAIL_BYTES = 15 * 1024 * 1024;

    public function pasta(): string
    {
        return storage_path('app/copias');
    }

    /** @return string full path of the new .sql.gz */
    public function criar(): string
    {
        File::ensureDirectoryExists($this->pasta());

        $ficheiro = $this->pasta().'/vencia-'.Carbon::now()->format('Y-m-d_His').'.sql.gz';
        file_put_contents($ficheiro, gzencode($this->sql(), 9));

        $this->limpar();

        return $ficheiro;
    }

    /** The whole database as SQL: structure first, then data, foreign keys off during import. */
    public function sql(): string
    {
        $mysql = DB::connection()->getDriverName() === 'mysql';
        $pdo = DB::connection()->getPdo();
        // Only this app's database: on MySQL getTables() also lists every other schema on the server.
        $base = DB::connection()->getDatabaseName();
        $tabelas = collect(Schema::getTables())
            ->filter(fn (array $t) => ! $mysql || ($t['schema'] ?? $base) === $base)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        $sql = '-- Cópia de segurança da Vencia, '.Carbon::now()->format('d/m/Y H:i')."\n"
            ."-- Restaurar: phpMyAdmin → base de dados (vazia) → Importar este ficheiro.\n\n"
            ."SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n";

        foreach ($tabelas as $tabela) {
            $sql .= "DROP TABLE IF EXISTS `{$tabela}`;\n".$this->criarTabela($tabela, $mysql).";\n\n";
        }

        // Throw-away runtime data: structure only, no rows.
        $semDados = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

        foreach (array_diff($tabelas, $semDados) as $tabela) {
            foreach (DB::table($tabela)->lazyById(500, $this->chave($tabela)) as $linha) {
                $linha = (array) $linha;
                $colunas = implode(', ', array_map(fn ($c) => "`{$c}`", array_keys($linha)));
                $valores = implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $linha));
                $sql .= "INSERT INTO `{$tabela}` ({$colunas}) VALUES ({$valores});\n";
            }
        }

        return $sql."\nSET FOREIGN_KEY_CHECKS=1;\n";
    }

    private function criarTabela(string $tabela, bool $mysql): string
    {
        if ($mysql) {
            return ((array) DB::selectOne("SHOW CREATE TABLE `{$tabela}`"))['Create Table'] ?? '';
        }

        return (string) DB::table('sqlite_master')->where('type', 'table')->where('name', $tabela)->value('sql');
    }

    /** lazyById needs an ordered unique column; a few framework tables have no "id". */
    private function chave(string $tabela): string
    {
        $colunas = Schema::getColumnListing($tabela);

        return match (true) {
            in_array('id', $colunas, true) => 'id',
            in_array('key', $colunas, true) => 'key',
            in_array('email', $colunas, true) => 'email',
            default => $colunas[0],
        };
    }

    /** Keeps only the most recent copies. */
    public function limpar(): void
    {
        collect(File::glob($this->pasta().'/vencia-*.sql.gz'))
            ->sortDesc()
            ->slice(self::MANTER)
            ->each(fn (string $f) => File::delete($f));
    }

    /** @return list<array{nome: string, bytes: int, data: Carbon}> newest first */
    public function existentes(): array
    {
        return collect(File::glob($this->pasta().'/vencia-*.sql.gz'))
            ->sortDesc()
            ->map(fn (string $f) => ['nome' => basename($f), 'bytes' => filesize($f), 'data' => Carbon::createFromTimestamp(filemtime($f))])
            ->values()
            ->all();
    }

    /** Sends the copy to the empresa email. Returns false (and logs why) when it couldn't. */
    public function enviarPorEmail(string $ficheiro, ?string $destino = null): bool
    {
        $destino ??= Configuracao::obter('empresa', [])['email'] ?? null;
        $smtp = trim((string) (Configuracao::obter('smtp', [])['servidor'] ?? ''));

        if (! $destino || ($smtp === '' && config('mail.default') === 'log')) {
            return false;
        }

        if (filesize($ficheiro) > self::MAX_EMAIL_BYTES) {
            Log::warning('Cópia de segurança demasiado grande para email: '.basename($ficheiro));

            return false;
        }

        try {
            Configuracao::aplicarSmtpEmTempoDeExecucao();
            Mail::raw(
                'Cópia de segurança diária da Vencia em anexo ('.basename($ficheiro).").\n\n"
                ."Guarda este email: para restaurar, importa o ficheiro numa base de dados vazia pelo phpMyAdmin.\n"
                .'O servidor guarda também as últimas '.self::MANTER.' cópias.',
                fn ($m) => $m->to($destino)->subject('Cópia de segurança da Vencia — '.Carbon::now()->format('d/m/Y'))->attach($ficheiro),
            );

            return true;
        } catch (Throwable $e) {
            Log::error('Falha ao enviar a cópia de segurança por email: '.$e->getMessage());

            return false;
        }
    }
}
