<?php

namespace Database\Seeders;

use App\Models\BibliotecaServico;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Historico;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Models\ServicoLembreteConfig;
use App\Models\Suspensao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Ports the fake dataset from ui_kits/subscription-manager/data.js field-for-field
 * (same clientes/servicos/pagamentos/historico), but with every date shifted so the
 * demo always looks current instead of pinned to the prototype's 2026-08/09 dates.
 *
 * The prototype's implicit "today" is 30/08/2026 (every servico's `dias` field is
 * exactly `vencimento - 30/08/2026`, e.g. s1: vencimento 02/09/2026, dias 3). All
 * fixed dates in the dataset are shifted by the same amount — the gap between that
 * fictitious reference date and the real today — so every relative day-count in the
 * original data (due-in-N-days, overdue-by-N-days, payment recency, history order)
 * is preserved exactly, not just servicos.vencimento.
 */
class DatabaseSeeder extends Seeder
{
    private int $shiftDays;

    public function run(): void
    {
        $referenciaFicticia = Carbon::create(2026, 8, 30);
        $this->shiftDays = (int) $referenciaFicticia->diffInDays(Carbon::today(), false);

        $this->seedAdmin();
        $this->seedConfiguracoes();
        $biblioteca = $this->seedBiblioteca();
        $clientes = $this->seedClientes();
        $servicos = $this->seedServicos($clientes, $biblioteca);
        $this->seedLembreteConfig($servicos);
        $this->seedPagamentos($clientes, $servicos);
        $this->seedHistoricoExemplo($clientes, $servicos);
        $this->seedSuspensaoDemo($servicos);
        $this->backfillMetodoHabitual($servicos);
    }

    /** Shifts a "dd/mm/Y" string from the prototype's date frame into today's, returns Y-m-d. */
    private function data(string $original): string
    {
        return Carbon::createFromFormat('d/m/Y', $original)->addDays($this->shiftDays)->toDateString();
    }

    private function seedAdmin(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'name' => 'Administrador',
                'password' => env('ADMIN_PASSWORD'), // the model's 'hashed' cast hashes this on save
                'email_verified_at' => Carbon::now(),
            ],
        );
    }

    private function seedConfiguracoes(): void
    {
        Configuracao::query()->updateOrCreate(['chave' => 'empresa'], [
            'valor' => [
                'nome' => 'Exemplo Serviços, Lda',
                'email' => 'cobrancas@example.com',
                'moeda' => 'MZN',
                'fuso_horario' => 'Africa/Maputo',
                'logo_path' => null,
            ],
        ]);

        Configuracao::query()->updateOrCreate(['chave' => 'lembretes'], [
            'valor' => [
                'verificacao_diaria' => true,
                'd30' => true,
                'd15' => true,
                'd7' => true,
                'd3' => true,
                'd1' => true,
                'no_dia' => true,
                'apos_vencimento' => true,
            ],
        ]);

        Configuracao::query()->updateOrCreate(['chave' => 'email_template_renovacao'], [
            'valor' => [
                'assunto' => 'O seu serviço [SERVIÇO] será renovado em breve',
                'corpo' => "Olá, [NOME].\n\nO seu serviço [SERVIÇO] será renovado em [DATA].\n\nValor da renovação: [VALOR].\n\nAtenciosamente,\n[NOME DA EMPRESA]",
            ],
        ]);

        Configuracao::query()->updateOrCreate(['chave' => 'email_template_servico_terminado'], [
            'valor' => [
                'assunto' => 'O seu serviço [SERVIÇO] foi suspenso',
                'corpo' => "Olá, [NOME].\n\nO seu serviço [SERVIÇO] foi suspenso em [DATA] por falta de renovação do pagamento.\n\nO acesso fica indisponível até regularização. Para reactivar, contacte-nos e efectue o pagamento de [VALOR].\n\nAtenciosamente,\n[NOME DA EMPRESA]",
            ],
        ]);
    }

    /** @return array<string, BibliotecaServico> keyed by nome */
    private function seedBiblioteca(): array
    {
        $descricoes = [
            'ChatGPT' => 'Acesso a conta de equipe ou individual, cobrado mensalmente.',
            'Claude' => 'Acesso individual, cobrado mensalmente.',
            'Canva' => 'Licença Pro para materiais gráficos.',
            'Google Workspace' => 'Email profissional e Drive por utilizador.',
            'Microsoft 365' => 'Office e email corporativo por utilizador.',
            'GitHub' => 'Repositórios privados e CI.',
            'Adobe' => 'Creative Cloud, licença única ou equipe.',
            'Dropbox' => 'Armazenamento partilhado com o cliente.',
            'Zoom' => 'Reuniões sem limite de tempo.',
            'Domínio .co.mz' => 'Registo e renovação anual, DNS gerido.',
            'Hospedagem Business' => 'Alojamento SSD com backups semanais.',
            'Email corporativo' => 'Contas de email no domínio do cliente.',
            'Manutenção de site' => 'Actualizações, backups e horas de alteração por mês.',
        ];

        $categorias = [
            'ChatGPT' => 'ia',
            'Claude' => 'ia',
            'Canva' => 'software',
            'Google Workspace' => 'email',
            'Microsoft 365' => 'software',
            'GitHub' => 'software',
            'Adobe' => 'software',
            'Dropbox' => 'software',
            'Zoom' => 'software',
            'Domínio .co.mz' => 'dominio',
            'Hospedagem Business' => 'hospedagem',
            'Email corporativo' => 'email',
            'Manutenção de site' => 'manutencao',
        ];

        $porNome = [];

        foreach ($categorias as $nome => $categoria) {
            $porNome[$nome] = BibliotecaServico::query()->updateOrCreate(
                ['nome' => $nome],
                [
                    'categoria' => $categoria,
                    'descricao_padrao' => $descricoes[$nome],
                    'arquivado' => false,
                ],
            );
        }

        return $porNome;
    }

    /** @return array<string, Cliente> keyed by the data.js id (c1..c5) */
    private function seedClientes(): array
    {
        $linhas = [
            ['id' => 'c1', 'nome' => 'João da Silva', 'email' => 'joao@email.com', 'tel' => '+258 84 512 0034', 'empresa' => 'Silva Consultoria', 'status' => 'activo', 'desde' => '12/03/2025'],
            ['id' => 'c2', 'nome' => 'Maria Costa', 'email' => 'maria.costa@email.com', 'tel' => '+258 82 771 9920', 'empresa' => null, 'status' => 'activo', 'desde' => '04/06/2025'],
            ['id' => 'c3', 'nome' => 'Carlos Manuel', 'email' => 'carlos@cmdigital.co.mz', 'tel' => '+258 87 330 1188', 'empresa' => 'CM Digital', 'status' => 'activo', 'desde' => '22/01/2026'],
            ['id' => 'c4', 'nome' => 'Ana Mucavele', 'email' => 'ana@mucavele.co.mz', 'tel' => '+258 84 220 7745', 'empresa' => 'Mucavele & Filhos', 'status' => 'activo', 'desde' => '09/09/2025'],
            ['id' => 'c5', 'nome' => 'Hélder Tembe', 'email' => 'helder.tembe@email.com', 'tel' => '+258 86 909 4412', 'empresa' => null, 'status' => 'inactivo', 'desde' => '17/11/2025'],
        ];

        $porId = [];

        foreach ($linhas as $l) {
            $porId[$l['id']] = Cliente::query()->updateOrCreate(
                ['email' => $l['email']],
                [
                    'nome' => $l['nome'],
                    'tel' => $l['tel'],
                    'empresa' => $l['empresa'],
                    'status' => $l['status'],
                    'desde' => $this->data($l['desde']),
                ],
            );
        }

        return $porId;
    }

    /**
     * @param array<string, Cliente> $clientes
     * @param array<string, BibliotecaServico> $biblioteca
     * @return array<string, Servico> keyed by the data.js id (s1..s11)
     */
    private function seedServicos(array $clientes, array $biblioteca): array
    {
        $linhas = [
            ['id' => 's1', 'clienteId' => 'c1', 'bib' => 'Hospedagem Business', 'descricao' => 'Alojamento do site institucional, 10 GB SSD, backups semanais.', 'nome' => 'Hospedagem', 'plano' => 'Plano Business', 'categoria' => 'hospedagem', 'valor' => 1500, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '02/09/2025', 'vencimento' => '02/09/2026', 'status' => 'a_vencer', 'dias' => 3],
            ['id' => 's2', 'clienteId' => 'c1', 'bib' => 'Domínio .co.mz', 'descricao' => 'Registo e renovação do domínio principal, DNS gerido por mim.', 'nome' => 'exemplo.co.mz', 'plano' => 'Domínio .co.mz', 'categoria' => 'dominio', 'valor' => 800, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '02/09/2025', 'vencimento' => '02/09/2027', 'status' => 'activo', 'dias' => 368],
            ['id' => 's3', 'clienteId' => 'c1', 'bib' => 'ChatGPT', 'descricao' => 'Acesso partilhado à conta de equipe — 2 utilizadores.', 'nome' => 'ChatGPT', 'plano' => 'Acesso Equipe', 'categoria' => 'ia', 'valor' => 500, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '01/09/2026', 'vencimento' => '01/10/2026', 'status' => 'activo', 'dias' => 32],
            ['id' => 's4', 'clienteId' => 'c2', 'bib' => 'ChatGPT', 'descricao' => 'Acesso individual para redacção de conteúdos.', 'nome' => 'ChatGPT', 'plano' => 'Acesso Equipe', 'categoria' => 'ia', 'valor' => 500, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '05/08/2026', 'vencimento' => '05/09/2026', 'status' => 'a_vencer', 'dias' => 6],
            ['id' => 's5', 'clienteId' => 'c2', 'bib' => 'Canva', 'descricao' => 'Licença Pro para materiais de marketing da loja.', 'nome' => 'Canva', 'plano' => 'Pro', 'categoria' => 'software', 'valor' => 1000, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '20/10/2025', 'vencimento' => '20/10/2026', 'status' => 'activo', 'dias' => 51],
            ['id' => 's6', 'clienteId' => 'c3', 'bib' => 'Domínio .co.mz', 'descricao' => 'Domínio da agência, renovação anual automática.', 'nome' => 'Domínio cmdigital.co.mz', 'plano' => 'Domínio .co.mz', 'categoria' => 'dominio', 'valor' => 800, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '08/09/2024', 'vencimento' => '08/09/2026', 'status' => 'a_vencer', 'dias' => 9],
            ['id' => 's7', 'clienteId' => 'c3', 'bib' => 'Manutenção de site', 'descricao' => 'Manutenção mensal do site: actualizações, backups e 4h de alterações.', 'nome' => 'Manutenção do site', 'plano' => '4h / mês', 'categoria' => 'manutencao', 'valor' => 2500, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '01/08/2026', 'vencimento' => '01/09/2026', 'status' => 'vencido', 'dias' => -1],
            ['id' => 's8', 'clienteId' => 'c4', 'bib' => 'Claude', 'descricao' => 'Acesso individual usado para análise de documentos.', 'nome' => 'Claude', 'plano' => 'Acesso individual', 'categoria' => 'ia', 'valor' => 700, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '15/08/2026', 'vencimento' => '15/09/2026', 'status' => 'activo', 'dias' => 16],
            ['id' => 's9', 'clienteId' => 'c4', 'bib' => 'Email corporativo', 'descricao' => '5 contas de email corporativo com webmail e antispam.', 'nome' => 'Email corporativo', 'plano' => '5 contas', 'categoria' => 'email', 'valor' => 1200, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '09/09/2025', 'vencimento' => '09/09/2026', 'status' => 'a_vencer', 'dias' => 10],
            ['id' => 's10', 'clienteId' => 'c5', 'bib' => 'ChatGPT', 'descricao' => 'Acesso suspenso por falta de pagamento; conta ainda não removida.', 'nome' => 'ChatGPT', 'plano' => 'Acesso Equipe', 'categoria' => 'ia', 'valor' => 500, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '05/07/2026', 'vencimento' => '05/08/2026', 'status' => 'suspenso', 'dias' => -25],
            ['id' => 's11', 'clienteId' => 'c2', 'bib' => 'Hospedagem Business', 'descricao' => 'Alojamento partilhado do site da loja online, 5 GB SSD.', 'nome' => 'Hospedagem', 'plano' => 'Plano Start', 'categoria' => 'hospedagem', 'valor' => 900, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '28/08/2026', 'vencimento' => '28/08/2027', 'status' => 'activo', 'dias' => 363],
        ];

        $porId = [];

        foreach ($linhas as $l) {
            // vencimento anchored to today + the original "dias" offset (the exact
            // relative due-in/overdue-by count from the prototype), inicio kept the
            // same distance from vencimento as in the original dataset.
            $vencimentoOriginal = Carbon::createFromFormat('d/m/Y', $l['vencimento']);
            $inicioOriginal = Carbon::createFromFormat('d/m/Y', $l['inicio']);
            $duracaoDias = $inicioOriginal->diffInDays($vencimentoOriginal);

            $vencimento = Carbon::today()->addDays($l['dias']);
            $inicio = $vencimento->copy()->subDays($duracaoDias);

            $porId[$l['id']] = Servico::query()->create([
                'cliente_id' => $clientes[$l['clienteId']]->id,
                'biblioteca_servico_id' => $biblioteca[$l['bib']]->id,
                'nome' => $l['nome'],
                'descricao' => $l['descricao'],
                'plano' => $l['plano'],
                'categoria' => $l['categoria'],
                'valor' => $l['valor'],
                'periodo' => $l['periodo'],
                'periodicidade' => $l['periodicidade'],
                'duracao_dias' => null,
                'inicio' => $inicio->toDateString(),
                'vencimento' => $vencimento->toDateString(),
                'status' => $l['status'],
            ]);
        }

        return $porId;
    }

    /** @param array<string, Servico> $servicos */
    private function seedLembreteConfig(array $servicos): void
    {
        // s1 demonstrates a per-servico override of the global default (matches the
        // prototype's ServiceDetailScreen example, where "1 dia antes" is the one
        // unchecked reminder). Every other seeded servico simply inherits the global
        // config (all flags null).
        ServicoLembreteConfig::query()->create([
            'servico_id' => $servicos['s1']->id,
            'd30' => true,
            'd15' => true,
            'd7' => true,
            'd3' => true,
            'd1' => false,
            'no_dia' => true,
            'apos_vencimento' => true,
        ]);

        foreach ($servicos as $id => $servico) {
            if ($id === 's1') {
                continue;
            }

            ServicoLembreteConfig::query()->create(['servico_id' => $servico->id]);
        }
    }

    /**
     * @param array<string, Cliente> $clientes
     * @param array<string, Servico> $servicos
     */
    private function seedPagamentos(array $clientes, array $servicos): void
    {
        $linhas = [
            ['data' => '30/08/2026', 'clienteId' => 'c2', 'servicoId' => 's11', 'valor' => 900, 'metodo' => 'mpesa', 'periodo' => '1 ano'],
            ['data' => '28/08/2026', 'clienteId' => 'c2', 'servicoId' => 's4', 'valor' => 500, 'metodo' => 'transferencia', 'periodo' => '1 mês'],
            ['data' => '20/08/2026', 'clienteId' => 'c4', 'servicoId' => 's8', 'valor' => 700, 'metodo' => 'emola', 'periodo' => '1 mês'],
            ['data' => '15/08/2026', 'clienteId' => 'c1', 'servicoId' => 's3', 'valor' => 500, 'metodo' => 'mpesa', 'periodo' => '1 mês'],
            ['data' => '12/08/2026', 'clienteId' => 'c3', 'servicoId' => 's7', 'valor' => 2500, 'metodo' => 'dinheiro', 'periodo' => '1 mês'],
            ['data' => '04/08/2026', 'clienteId' => 'c4', 'servicoId' => 's9', 'valor' => 1200, 'metodo' => 'transferencia', 'periodo' => '1 ano'],
            ['data' => '18/08/2026', 'clienteId' => 'c1', 'servicoId' => 's1', 'valor' => 1500, 'metodo' => 'mpesa', 'periodo' => '1 ano'],
            ['data' => '14/08/2026', 'clienteId' => 'c2', 'servicoId' => 's5', 'valor' => 1000, 'metodo' => 'emola', 'periodo' => '1 ano'],
            ['data' => '11/08/2026', 'clienteId' => 'c3', 'servicoId' => 's6', 'valor' => 800, 'metodo' => 'transferencia', 'periodo' => '1 ano'],
            ['data' => '08/08/2026', 'clienteId' => 'c4', 'servicoId' => 's8', 'valor' => 700, 'metodo' => 'mpesa', 'periodo' => '1 mês'],
            ['data' => '02/08/2026', 'clienteId' => 'c1', 'servicoId' => 's2', 'valor' => 800, 'metodo' => 'mpesa', 'periodo' => '1 ano'],
        ];

        foreach ($linhas as $l) {
            Pagamento::query()->create([
                'data' => $this->data($l['data']),
                'cliente_id' => $clientes[$l['clienteId']]->id,
                'servico_id' => $servicos[$l['servicoId']]->id,
                'valor' => $l['valor'],
                'metodo' => $l['metodo'],
                'periodo' => $l['periodo'],
            ]);
        }
    }

    /**
     * @param array<string, Cliente> $clientes
     * @param array<string, Servico> $servicos
     */
    private function seedHistoricoExemplo(array $clientes, array $servicos): void
    {
        // Ported verbatim from data.js's `historico` array, which narrates s1's
        // (Hospedagem, cliente c1) life from creation to its most recent reminders.
        $linhas = [
            ['data' => '01/09/2025', 'title' => 'Serviço criado', 'description' => 'Hospedagem — Plano Business', 'kind' => 'criado'],
            ['data' => '02/09/2025', 'title' => 'Pagamento recebido', 'description' => '1.500 MZN — M-Pesa · 1 ano', 'kind' => 'pagamento'],
            ['data' => '02/09/2025', 'title' => 'Serviço activado', 'description' => null, 'kind' => 'activado'],
            ['data' => '03/08/2026', 'title' => 'Lembrete enviado', 'description' => '30 dias antes — joao@email.com', 'kind' => 'lembrete'],
            ['data' => '18/08/2026', 'title' => 'Lembrete enviado', 'description' => '15 dias antes — joao@email.com', 'kind' => 'lembrete'],
            ['data' => '26/08/2026', 'title' => 'Lembrete enviado', 'description' => '7 dias antes — joao@email.com', 'kind' => 'lembrete'],
        ];

        foreach ($linhas as $l) {
            Historico::query()->create([
                'cliente_id' => $clientes['c1']->id,
                'servico_id' => $servicos['s1']->id,
                'occurred_at' => $this->data($l['data']),
                'title' => $l['title'],
                'description' => $l['description'],
                'kind' => $l['kind'],
            ]);
        }

        // Baseline "Serviço criado" record for every other seeded servico, so the
        // append-only history log isn't empty for them.
        foreach ($servicos as $id => $servico) {
            if ($id === 's1') {
                continue;
            }

            Historico::query()->create([
                'cliente_id' => $servico->cliente_id,
                'servico_id' => $servico->id,
                'occurred_at' => $servico->inicio,
                'title' => 'Serviço criado',
                'description' => sprintf('%s — %s', $servico->nome, $servico->plano),
                'kind' => 'criado',
            ]);
        }
    }

    /** @param array<string, Servico> $servicos */
    private function seedSuspensaoDemo(array $servicos): void
    {
        $s10 = $servicos['s10'];

        Suspensao::query()->create([
            'servico_id' => $s10->id,
            'data' => $s10->vencimento->toDateString(),
            'motivo' => 'pagamento_nao_renovado',
            'observacao' => 'Sem resposta do cliente após os lembretes de vencimento.',
            'enviou_email' => true,
        ]);

        Historico::query()->create([
            'cliente_id' => $s10->cliente_id,
            'servico_id' => $s10->id,
            'occurred_at' => $s10->vencimento,
            'title' => 'Serviço suspenso',
            'description' => 'Pagamento não renovado.',
            'kind' => 'suspenso',
        ]);
    }

    /** @param array<string, Servico> $servicos */
    private function backfillMetodoHabitual(array $servicos): void
    {
        foreach ($servicos as $servico) {
            $ultimoMetodo = Pagamento::query()
                ->where('servico_id', $servico->id)
                ->orderByDesc('data')
                ->value('metodo');

            if ($ultimoMetodo !== null) {
                $servico->update(['metodo_habitual' => $ultimoMetodo]);
            }
        }
    }
}
