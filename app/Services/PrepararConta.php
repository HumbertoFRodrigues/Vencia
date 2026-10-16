<?php

namespace App\Services;

use App\Enums\MetodoPagamento;
use App\Enums\Papel;
use App\Models\BibliotecaServico;
use App\Models\Configuracao;
use App\Models\Conta;
use App\Models\MetodoPagamentoOpcao;
use App\Models\Plano;
use App\Models\User;
use App\Support\ContaActual;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Creates a conta for its dono with everything a new business needs on day
 * one: default settings and email templates, the payment methods and the
 * service library. padroes() only adds what is missing, so it is safe to
 * run again on an existing conta (AdminSeeder does).
 */
class PrepararConta
{
    public function criar(User $dono, string $nome, ?Plano $plano = null, ?Carbon $planoAte = null, bool $emTeste = false): Conta
    {
        return DB::transaction(function () use ($dono, $nome, $plano, $planoAte, $emTeste): Conta {
            $conta = Conta::create([
                'nome' => $nome,
                'plano_id' => ($plano ?? Plano::gratis())->id,
                'plano_ate' => $planoAte?->toDateString(),
                'em_teste' => $emTeste,
            ]);

            $conta->membros()->attach($dono->id, ['papel' => Papel::Dono->value]);
            $dono->forceFill(['conta_actual_id' => $conta->id])->save();
            $dono->esquecerPapeis();

            $this->padroes($conta, $dono->email);

            return $conta;
        });
    }

    public function padroes(Conta $conta, ?string $email = null): void
    {
        ContaActual::usar($conta, function (Conta $conta) use ($email): void {
            $this->configuracoes($conta->nome, $email);
            $this->metodosPagamento();
            $this->biblioteca();
        });
    }

    private function configuracoes(string $nome, ?string $email): void
    {
        $padrao = [
            'empresa' => [
                'nome' => $nome,
                'email' => $email,
                'moeda' => 'MZN',
                'fuso_horario' => 'Africa/Maputo',
                'logo_path' => null,
            ],
            'lembretes' => [
                'verificacao_diaria' => true,
                'd30' => true, 'd15' => true, 'd7' => true, 'd3' => true, 'd1' => true,
                'no_dia' => true, 'apos_vencimento' => true,
                'antecedencia' => ServicoStatusService::ANTECEDENCIA_PADRAO,
            ],
            // Never says "será renovado": there is no automatic billing, the
            // client pays and the business clicks "Renovar". The before/on/after
            // nuance is added by LembreteVencimentoMail from the real countdown.
            'email_template_renovacao' => [
                'assunto' => 'O seu serviço [SERVIÇO] — vencimento a [DATA]',
                'corpo' => "Olá, [NOME].\n\nO seu serviço [SERVIÇO] vence em [DATA].\n\nO acesso é suspenso caso o pagamento não seja renovado. Para renovar, contacte-nos e efectue o pagamento de [VALOR].\n\nAtenciosamente,\n[NOME DA EMPRESA]",
            ],
            'email_template_servico_terminado' => [
                'assunto' => 'O seu serviço [SERVIÇO] foi suspenso',
                'corpo' => "Olá, [NOME].\n\nO seu serviço [SERVIÇO] foi suspenso em [DATA] por falta de renovação do pagamento.\n\nO acesso fica indisponível até regularização. Para reactivar, contacte-nos e efectue o pagamento de [VALOR].\n\nAtenciosamente,\n[NOME DA EMPRESA]",
            ],
        ];

        foreach ($padrao as $chave => $valor) {
            Configuracao::query()->firstOrCreate(['chave' => $chave], ['valor' => $valor]);
        }
    }

    /**
     * The 5 well-known métodos as manageable rows. `nome` is the enum's code
     * (mpesa/emola/...), the literal string pagamentos.metodo holds.
     */
    private function metodosPagamento(): void
    {
        foreach (MetodoPagamento::cases() as $case) {
            MetodoPagamentoOpcao::query()->firstOrCreate(['nome' => $case->value], ['arquivado' => false]);
        }
    }

    private function biblioteca(): void
    {
        // A mix of everyday recurring services, not only IT: each business edits, archives or adds its own.
        $servicos = [
            'Mensalidade' => ['saude', 'Valor fixo cobrado todos os meses (ginásio, escola, clube, associação).'],
            'Explicações' => ['educacao', 'Aulas ou explicações cobradas por mês.'],
            'Renda' => ['imoveis', 'Arrendamento de casa, loja ou escritório.'],
            'Contabilidade' => ['financas', 'Avença mensal de contabilidade e impostos.'],
            'Consultoria' => ['consultoria', 'Horas de consultoria ou acompanhamento mensal.'],
            'Seguro' => ['seguros', 'Prémio de seguro com renovação anual.'],
            'Limpeza' => ['manutencao', 'Serviço de limpeza regular.'],
            'Manutenção' => ['manutencao', 'Manutenção preventiva de equipamentos ou instalações.'],
            'Segurança e vigilância' => ['seguranca', 'Guarda, alarme ou monitorização mensal.'],
            'Internet' => ['telefonia', 'Plano de internet ou comunicações.'],
            'Alojamento de site' => ['hospedagem', 'Alojamento web com renovação anual.'],
            'Domínio .co.mz' => ['dominio', 'Registo e renovação anual do domínio.'],
            'Email profissional' => ['email', 'Contas de email no domínio do cliente.'],
        ];

        foreach ($servicos as $nome => [$categoria, $descricao]) {
            BibliotecaServico::query()->firstOrCreate(['nome' => $nome], [
                'categoria' => $categoria,
                'descricao_padrao' => $descricao,
                'arquivado' => false,
            ]);
        }
    }
}
