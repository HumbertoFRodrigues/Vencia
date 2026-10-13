<?php

namespace Database\Seeders;

use App\Enums\MetodoPagamento;
use App\Models\BibliotecaServico;
use App\Models\Configuracao;
use App\Models\MetodoPagamentoOpcao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * What a real installation needs and nothing more: the admin account from
 * ADMIN_EMAIL/ADMIN_PASSWORD, default settings and email templates, the
 * payment methods and the service library — no demo clients.
 *
 * Safe to run any number of times: it only creates what is missing and
 * never overwrites a password, a setting or a library entry already there
 * (production runs it from a cPanel cron that may fire more than once).
 * DatabaseSeeder calls it first and then adds the demo data on top.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->admin();
        $this->configuracoes();
        $this->metodosPagamento();
        $this->biblioteca();
    }

    private function admin(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (! $email || ! $password) {
            throw new RuntimeException('Defina ADMIN_EMAIL e ADMIN_PASSWORD no .env antes de criar a conta de administrador.');
        }

        User::query()->firstOrCreate(['email' => $email], [
            'name' => 'Administrador',
            'password' => $password, // the model's 'hashed' cast hashes this on save
            'email_verified_at' => Carbon::now(),
        ]);
    }

    private function configuracoes(): void
    {
        $padrao = [
            'empresa' => [
                'nome' => config('app.name', 'Vencia'),
                'email' => config('app.admin_email'),
                'moeda' => 'MZN',
                'fuso_horario' => 'Africa/Maputo',
                'logo_path' => null,
            ],
            'lembretes' => [
                'verificacao_diaria' => true,
                'd30' => true, 'd15' => true, 'd7' => true, 'd3' => true, 'd1' => true,
                'no_dia' => true, 'apos_vencimento' => true,
            ],
            // Never says "será renovado": there is no automatic billing, the
            // client pays and the admin clicks "Renovar". The before/on/after
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
        $servicos = [
            'ChatGPT' => ['ia', 'Acesso a conta de equipe ou individual, cobrado mensalmente.'],
            'Claude' => ['ia', 'Acesso individual, cobrado mensalmente.'],
            'Canva' => ['software', 'Licença Pro para materiais gráficos.'],
            'Google Workspace' => ['email', 'Email profissional e Drive por utilizador.'],
            'Microsoft 365' => ['software', 'Office e email corporativo por utilizador.'],
            'GitHub' => ['software', 'Repositórios privados e CI.'],
            'Adobe' => ['software', 'Creative Cloud, licença única ou equipe.'],
            'Dropbox' => ['software', 'Armazenamento partilhado com o cliente.'],
            'Zoom' => ['software', 'Reuniões sem limite de tempo.'],
            'Domínio .co.mz' => ['dominio', 'Registo e renovação anual, DNS gerido.'],
            'Hospedagem Business' => ['hospedagem', 'Alojamento SSD com backups semanais.'],
            'Email corporativo' => ['email', 'Contas de email no domínio do cliente.'],
            'Manutenção de site' => ['manutencao', 'Actualizações, backups e horas de alteração por mês.'],
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
