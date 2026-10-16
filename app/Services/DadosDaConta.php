<?php

namespace App\Services;

use App\Enums\Papel;
use App\Models\ActividadeAdmin;
use App\Models\BibliotecaServico;
use App\Models\CategoriaPessoal;
use App\Models\Cliente;
use App\Models\Conta;
use App\Models\ContaPessoal;
use App\Models\FixoPessoal;
use App\Models\Historico;
use App\Models\MetaPessoal;
use App\Models\MovimentoPessoal;
use App\Models\Pagamento;
use App\Models\Retirada;
use App\Models\Servico;
use App\Models\User;
use App\Support\ContaActual;
use App\Support\Csv;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * What the privacy policy promises: a copy of your data (the business's,
 * for its dono; your personal panel, for anyone) as CSV files in a ZIP, and
 * deleting your account. Also the one place a whole conta is deleted.
 */
class DadosDaConta
{
    /** @return string path of a temporary .zip, removed after the download */
    public function zipEmpresa(Conta $conta): string
    {
        return ContaActual::usar($conta, fn () => $this->zip('vencia-empresa', [
            'clientes' => Cliente::query(),
            'servicos' => Servico::query(),
            'pagamentos' => Pagamento::query(),
            'historico' => Historico::query(),
            'retiradas' => Retirada::query(),
            'biblioteca-servicos' => BibliotecaServico::query(),
        ], ['conta_id', 'logo_path']));
    }

    /** The personal panel of the logged-in user (the personal models are scoped to them). */
    public function zipPessoal(): string
    {
        return $this->zip('vencia-pessoal', [
            'movimentos' => MovimentoPessoal::query(),
            'fixos-mensais' => FixoPessoal::query(),
            'categorias' => CategoriaPessoal::query(),
            'saldos' => ContaPessoal::query(),
            'metas' => MetaPessoal::query(),
        ], ['user_id']);
    }

    /**
     * @param  array<string, Builder>  $tabelas
     * @param  list<string>  $esconder  internal columns left out of the files
     */
    private function zip(string $prefixo, array $tabelas, array $esconder): string
    {
        $pasta = storage_path('app/exportacoes');
        File::ensureDirectoryExists($pasta);

        // Downloads are one-off: anything older than an hour is a leftover.
        foreach (File::glob($pasta.'/*.zip') as $antigo) {
            if (filemtime($antigo) < time() - 3600) {
                File::delete($antigo);
            }
        }

        $caminho = $pasta.'/'.$prefixo.'-'.Carbon::now()->format('Y-m-d_His').'-'.Str::random(6).'.zip';

        $zip = new ZipArchive;
        if ($zip->open($caminho, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o ficheiro de exportação.');
        }

        foreach ($tabelas as $nome => $consulta) {
            $linhas = $consulta->orderBy($consulta->getModel()->getKeyName())->get()
                ->map(fn ($m) => collect($m->getAttributes())->except($esconder)->all());
            $cabecalho = $linhas->isNotEmpty() ? array_keys($linhas->first()) : ['(sem registos)'];

            $zip->addFromString($nome.'.csv', Csv::conteudo($cabecalho, $linhas->map(fn ($l) => array_values($l))));
        }

        $zip->addFromString('LEIA-ME.txt', 'Exportação da Vencia, '.Carbon::now()->format('d/m/Y H:i').".\r\n"
            ."Cada ficheiro .csv abre no Excel ou no Google Sheets (separador: ponto e vírgula).\r\n");
        $zip->close();

        return $caminho;
    }

    /**
     * Deletes a conta and everything in it. Payments are protected against
     * deletion (restrictOnDelete), so the business rows go in order first.
     * The members' personal panels are theirs: "Retirada da empresa" entries
     * stay, just unlinked.
     */
    public function apagarConta(Conta $conta): void
    {
        $faturas = $conta->faturas()->pluck('id');

        DB::transaction(function () use ($conta): void {
            Pagamento::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            $retiradas = Retirada::withoutGlobalScope('conta')->where('conta_id', $conta->id)->pluck('id');
            DB::table('movimentos_pessoais')->whereIn('retirada_id', $retiradas)->update(['retirada_id' => null]);
            Retirada::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            Historico::withoutGlobalScope('conta')->where('conta_id', $conta->id)->toBase()->delete();
            Servico::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            Cliente::withoutGlobalScope('conta')->where('conta_id', $conta->id)->delete();
            $conta->delete();
        });

        Storage::disk('public')->deleteDirectory('contas/'.$conta->id);
        foreach ($faturas as $faturaId) {
            Storage::disk('local')->deleteDirectory('faturas/'.$faturaId);
        }
    }

    /**
     * What deleting this user would do, or why it can't happen yet.
     *
     * @return array{bloqueios: list<string>, apagar: list<Conta>, sair: list<Conta>}
     */
    public function planoParaApagar(User $user): array
    {
        $plano = ['bloqueios' => [], 'apagar' => [], 'sair' => []];

        if ($user->super_admin && User::query()->where('super_admin', true)->count() === 1) {
            $plano['bloqueios'][] = 'És o único super-admin da plataforma. Torna outra pessoa super-admin primeiro.';
        }

        foreach ($user->contas()->get() as $conta) {
            $donos = $conta->membros()->wherePivot('papel', Papel::Dono->value)->count();
            $outros = $conta->membros()->where('users.id', '!=', $user->id)->count();

            if ($conta->pivot->papel === Papel::Dono->value && $donos === 1 && $outros > 0) {
                $plano['bloqueios'][] = 'És o único dono de "'.$conta->nome.'", que tem mais membros. Em Equipa, torna outra pessoa dona (ou remove os membros) primeiro.';
            } elseif ($outros === 0) {
                $plano['apagar'][] = $conta;
            } else {
                $plano['sair'][] = $conta;
            }
        }

        return $plano;
    }

    /** @throws RuntimeException when planoParaApagar() has blockers */
    public function apagarUtilizador(User $user): void
    {
        $plano = $this->planoParaApagar($user);
        if ($plano['bloqueios'] !== []) {
            throw new RuntimeException($plano['bloqueios'][0]);
        }

        foreach ($plano['apagar'] as $conta) {
            $this->apagarConta($conta);
        }

        DB::transaction(function () use ($user, $plano): void {
            // Withdrawals stay in the businesses that go on, under one of their owners.
            foreach ($plano['sair'] as $conta) {
                $outroDono = $conta->membros()->wherePivot('papel', Papel::Dono->value)->where('users.id', '!=', $user->id)->value('users.id');
                Retirada::withoutGlobalScope('conta')->where('conta_id', $conta->id)->where('user_id', $user->id)->update(['user_id' => $outroDono]);
            }

            // Logged before the user row goes (the log keeps it, with the user set to null).
            ActividadeAdmin::registar('user.apagou_conta', 'user:'.$user->id, $user->email);
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->delete(); // personal panel rows and memberships go by cascade
        });
    }
}
