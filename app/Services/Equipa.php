<?php

namespace App\Services;

use App\Enums\Papel;
use App\Mail\PlataformaMail;
use App\Models\Configuracao;
use App\Models\Conta;
use App\Models\Convite;
use App\Models\User;
use App\Support\ContaActual;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * A conta's team: inviting by email, accepting, changing a member's role
 * and removing them. A conta always keeps at least one dono.
 */
class Equipa
{
    public const DIAS_CONVITE = 7;

    /** @throws RuntimeException with a message for the user */
    public function convidar(Conta $conta, string $email, Papel $papel, User $por): Convite
    {
        $email = mb_strtolower(trim($email));

        if ($conta->membros()->where('email', $email)->exists()) {
            throw new RuntimeException('Esta pessoa já faz parte da equipa.');
        }

        // A new invite to the same address replaces the old one (and doesn't count twice).
        Convite::query()->where('conta_id', $conta->id)->where('email', $email)->whereNull('aceite_em')->delete();

        if (! ContaActual::usar($conta, fn () => app(Limites::class)->pode('membros'))) {
            throw new RuntimeException('O teu plano não inclui mais membros. Passa para o Pro em Plano para convidar mais pessoas.');
        }

        $token = Str::random(48);
        $convite = Convite::create([
            'conta_id' => $conta->id,
            'email' => $email,
            'papel' => $papel,
            'token' => hash('sha256', $token),
            'convidado_por' => $por->id,
            'expira_em' => Carbon::now()->addDays(self::DIAS_CONVITE),
        ]);

        Configuracao::aplicarSmtpDaPlataforma();
        Mail::to($email)->send(new PlataformaMail('Convite para '.$conta->nome, [
            'Olá.',
            $por->name.' convidou-te para a equipa de '.$conta->nome.' na Vencia, com o papel '.$papel->label().': '.mb_lcfirst($papel->descricao()),
            'Abre esta ligação para aceitar (é válida durante '.self::DIAS_CONVITE.' dias). Se ainda não tens conta, crias uma em menos de um minuto:',
        ], route('convite.mostrar', $token)));

        return $convite;
    }

    /** The conta's plan still has room for this person (a plan may have ended since the invite was sent). */
    public function cabe(Convite $convite, ?User $user = null): bool
    {
        $conta = $convite->conta;
        if ($user && $user->contas()->whereKey($conta->id)->exists()) {
            return true;
        }

        $limite = $conta->planoEfectivo()->limite('membros');

        return $limite === null || $conta->membros()->count() < $limite;
    }

    /** @throws RuntimeException when the plan no longer has room */
    public function aceitar(Convite $convite, User $user): void
    {
        if (! $this->cabe($convite, $user)) {
            throw new RuntimeException('A equipa de '.$convite->conta->nome.' já está completa no plano actual. Pede a quem te convidou para passar ao Pro ou libertar um lugar.');
        }

        DB::transaction(function () use ($convite, $user): void {
            $conta = $convite->conta;

            if (! $user->contas()->whereKey($conta->id)->exists()) {
                $user->contas()->attach($conta->id, ['papel' => $convite->papel->value]);
            }

            $convite->forceFill(['aceite_em' => Carbon::now()])->save();
            $user->forceFill(['conta_actual_id' => $conta->id])->save();
            $user->esquecerPapeis();
        });
    }

    /** @throws RuntimeException */
    public function mudarPapel(Conta $conta, User $membro, Papel $papel): void
    {
        if ($papel !== Papel::Dono && $membro->papelNa($conta) === Papel::Dono && $this->donos($conta) <= 1) {
            throw new RuntimeException('A conta precisa de pelo menos um dono. Torna outra pessoa dona primeiro.');
        }

        $conta->membros()->updateExistingPivot($membro->id, ['papel' => $papel->value]);
        $membro->esquecerPapeis();
    }

    /** @throws RuntimeException */
    public function remover(Conta $conta, User $membro): void
    {
        if ($membro->papelNa($conta) === Papel::Dono && $this->donos($conta) <= 1) {
            throw new RuntimeException('Não podes remover o único dono da conta.');
        }

        $conta->membros()->detach($membro->id);

        if ($membro->conta_actual_id === $conta->id) {
            $membro->forceFill(['conta_actual_id' => null])->save();
        }
        $membro->esquecerPapeis();
    }

    private function donos(Conta $conta): int
    {
        return $conta->membros()->wherePivot('papel', Papel::Dono->value)->count();
    }
}
