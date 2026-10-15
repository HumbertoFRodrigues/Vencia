<?php

namespace App\Services;

use App\Contracts\GatewayPagamento;
use App\Mail\PlataformaMail;
use App\Models\ActividadeAdmin;
use App\Models\Configuracao;
use App\Models\Conta;
use App\Models\Fatura;
use App\Models\Plano;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Paying for plans: the customer asks for a plan (a pendente fatura), sends
 * the payment reference/proof (em_analise), the super-admin approves
 * (paga — the plan is extended) or rejects it. marcarPaga() is the single
 * place a plan gets extended, so an automatic gateway reuses it as-is.
 */
class Faturacao
{
    public const CICLOS = ['mensal' => 'Mensal', 'anual' => 'Anual'];

    public function __construct(private GatewayPagamento $gateway) {}

    public function gateway(): GatewayPagamento
    {
        return $this->gateway;
    }

    public function moeda(): string
    {
        return Configuracao::plataforma('faturacao', [])['moeda'] ?? 'MZN';
    }

    /** @throws RuntimeException */
    public function pedir(Conta $conta, Plano $plano, string $ciclo, User $por): Fatura
    {
        if (! array_key_exists($ciclo, self::CICLOS) || ! $plano->activo || $plano->preco($ciclo) <= 0) {
            throw new RuntimeException('Este plano não está disponível com esse ciclo.');
        }

        return DB::transaction(function () use ($conta, $plano, $ciclo, $por): Fatura {
            // One open request at a time: a new choice replaces an unpaid one.
            $conta->faturas()->where('estado', Fatura::PENDENTE)->update(['estado' => Fatura::CANCELADA]);

            return $conta->faturas()->create([
                'plano_id' => $plano->id,
                'ciclo' => $ciclo,
                'valor' => $plano->preco($ciclo),
                'estado' => Fatura::PENDENTE,
                'gateway' => $this->gateway->nome(),
                'pedida_por' => $por->id,
            ]);
        });
    }

    /** @throws RuntimeException */
    public function enviarComprovativo(Fatura $fatura, string $metodo, ?string $referencia, ?UploadedFile $comprovativo): void
    {
        if ($fatura->estado !== Fatura::PENDENTE) {
            throw new RuntimeException('Esta fatura já não está à espera de pagamento.');
        }

        if (blank($referencia) && ! $comprovativo) {
            throw new RuntimeException('Indica a referência da transacção ou anexa o comprovativo.');
        }

        $caminho = $comprovativo?->store('faturas/'.$fatura->id, 'local');

        $fatura->update([
            'metodo' => $metodo,
            'referencia' => $referencia ? trim($referencia) : null,
            'comprovativo_path' => $caminho ?? $fatura->comprovativo_path,
            'estado' => Fatura::EM_ANALISE,
            'enviada_em' => Carbon::now(),
        ]);

        $this->avisarAdmins($fatura);
    }

    public function aprovar(Fatura $fatura, User $admin): void
    {
        if (! in_array($fatura->estado, [Fatura::PENDENTE, Fatura::EM_ANALISE], true)) {
            throw new RuntimeException('Só uma fatura por pagar ou em análise pode ser aprovada.');
        }

        $this->marcarPaga($fatura, $admin);
    }

    /** Extends the conta's plan by one cycle, starting where the current paid period ends. */
    public function marcarPaga(Fatura $fatura, ?User $aprovador = null, ?string $gatewayRef = null): void
    {
        DB::transaction(function () use ($fatura, $aprovador, $gatewayRef): void {
            $conta = Conta::query()->lockForUpdate()->findOrFail($fatura->conta_id);
            $hoje = Carbon::today();

            $continua = $conta->plano_id === $fatura->plano_id && ! $conta->em_teste && $conta->plano_ate && $conta->plano_ate->gte($hoje);
            $inicio = $continua ? $conta->plano_ate->copy()->addDay() : $hoje;
            $fim = ($fatura->ciclo === 'anual' ? $inicio->copy()->addYear() : $inicio->copy()->addMonth())->subDay();

            $fatura->update([
                'estado' => Fatura::PAGA,
                'paga_em' => Carbon::now(),
                'aprovada_por' => $aprovador?->id,
                'gateway_ref' => $gatewayRef ?? $fatura->gateway_ref,
                'periodo_inicio' => $inicio->toDateString(),
                'periodo_fim' => $fim->toDateString(),
            ]);

            $conta->forceFill([
                'plano_id' => $fatura->plano_id,
                'plano_ate' => $fim->toDateString(),
                'em_teste' => false,
                'avisos_plano' => null,
            ])->save();

            ActividadeAdmin::registar('fatura.aprovada', 'conta:'.$conta->id, $fatura->referenciaInterna().' — '.$fatura->plano->nome.' até '.$fim->format('d/m/Y'));
        });

        $fatura->refresh();
        $this->avisarDono($fatura->conta, 'Pagamento confirmado', [
            'Recebemos o pagamento da fatura '.$fatura->referenciaInterna().' ('.$this->valor($fatura).').',
            'O plano '.$fatura->plano->nome.' da conta '.$fatura->conta->nome.' está activo até '.$fatura->periodo_fim->format('d/m/Y').'. Obrigado!',
        ]);
    }

    public function rejeitar(Fatura $fatura, User $admin, string $motivo): void
    {
        $fatura->update(['estado' => Fatura::REJEITADA, 'nota' => $motivo, 'aprovada_por' => $admin->id]);
        ActividadeAdmin::registar('fatura.rejeitada', 'conta:'.$fatura->conta_id, $fatura->referenciaInterna().' — '.$motivo);

        $this->avisarDono($fatura->conta, 'Não conseguimos confirmar o pagamento', [
            'Não foi possível confirmar o pagamento da fatura '.$fatura->referenciaInterna().' ('.$this->valor($fatura).').',
            'Motivo: '.$motivo,
            'Podes pedir o plano outra vez em Plano e enviar a referência correcta. Se já pagaste, responde a este email com o comprovativo.',
        ]);
    }

    /**
     * The daily job: warns the dono 7 days and 1 day before a paid plan ends,
     * and moves contas whose plan ended back to Grátis.
     *
     * @return array{avisos: int, expiradas: int}
     */
    public function verificarPlanos(): array
    {
        $resumo = ['avisos' => 0, 'expiradas' => 0];
        $hoje = Carbon::today();
        $gratis = Plano::gratis();

        Conta::query()->whereNotNull('plano_ate')->with('plano')->each(function (Conta $conta) use (&$resumo, $hoje, $gratis): void {
            $dias = (int) $hoje->diffInDays($conta->plano_ate, false);

            if ($dias < 0) {
                $anterior = $conta->plano?->nome ?? 'Pro';
                $conta->forceFill(['plano_id' => $gratis->id, 'plano_ate' => null, 'em_teste' => false, 'avisos_plano' => null])->save();
                ActividadeAdmin::registar('plano.expirou', 'conta:'.$conta->id, $anterior.' → '.$gratis->nome);
                $this->avisarDono($conta, 'O teu plano terminou', [
                    'O plano '.$anterior.' da conta '.$conta->nome.' terminou e a conta passou para o plano '.$gratis->nome.'.',
                    'Os teus dados ficam todos guardados. Só não consegues adicionar mais do que o plano '.$gratis->nome.' permite. Para voltar ao '.$anterior.', vai a Plano.',
                ]);
                $resumo['expiradas']++;

                return;
            }

            foreach ([7, 1] as $aviso) {
                $chave = $aviso.'@'.$conta->plano_ate->toDateString();
                if ($dias <= $aviso && ! in_array($chave, $conta->avisos_plano ?? [], true)) {
                    $conta->forceFill(['avisos_plano' => array_merge($conta->avisos_plano ?? [], [$chave])])->save();
                    $this->avisarDono($conta, $conta->em_teste ? 'O teu período de teste está a acabar' : 'O teu plano está a acabar', [
                        'O plano '.($conta->plano?->nome ?? '').' da conta '.$conta->nome.' termina a '.$conta->plano_ate->format('d/m/Y').'.',
                        'Para continuar sem interrupção, renova em Plano. Se não renovares, a conta passa para o plano Grátis e os dados ficam guardados.',
                    ], route('conta.plano'));
                    $resumo['avisos']++;

                    break;
                }
            }
        });

        return $resumo;
    }

    public function valor(Fatura $fatura): string
    {
        return number_format((float) $fatura->valor, 2, ',', '.').' '.$this->moeda();
    }

    public function urlComprovativo(Fatura $fatura): ?string
    {
        return $fatura->comprovativo_path && Storage::disk('local')->exists($fatura->comprovativo_path)
            ? route('faturas.comprovativo', $fatura)
            : null;
    }

    private function avisarAdmins(Fatura $fatura): void
    {
        $admins = User::query()->where('super_admin', true)->pluck('email')->all();
        if ($admins === []) {
            return;
        }

        $this->enviar($admins, 'Pagamento para confirmar: '.$fatura->conta->nome, [
            'A conta '.$fatura->conta->nome.' enviou o pagamento da fatura '.$fatura->referenciaInterna().'.',
            'Plano '.$fatura->plano->nome.' ('.(self::CICLOS[$fatura->ciclo] ?? $fatura->ciclo).'), '.$this->valor($fatura).'. Método: '.$fatura->metodoLabel().'. Referência: '.($fatura->referencia ?: '—').'.',
        ], route('admin.faturas'));
    }

    /** @param list<string> $paragrafos */
    private function avisarDono(Conta $conta, string $assunto, array $paragrafos, ?string $url = null): void
    {
        $dono = $conta->dono();
        if ($dono) {
            $this->enviar([$dono->email], $assunto, array_merge(['Olá, '.$dono->name.'.'], $paragrafos), $url);
        }
    }

    /** Never throws: a failed email must not undo a payment or an expiry. */
    private function enviar(array $para, string $assunto, array $paragrafos, ?string $url = null): void
    {
        try {
            Configuracao::aplicarSmtpDaPlataforma();
            Mail::to($para)->send(new PlataformaMail($assunto, $paragrafos, $url));
        } catch (Throwable $e) {
            Log::error('Falha ao enviar "'.$assunto.'": '.$e->getMessage());
        }
    }
}
