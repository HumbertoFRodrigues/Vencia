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
use App\Support\ContaActual;
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

        $this->call(AdminSeeder::class);
        // The demo business is the admin's own conta.
        ContaActual::definir(User::query()->where('email', config('app.admin_email'))->firstOrFail()->contaActual());
        $this->seedEmpresaDemo();
        $biblioteca = BibliotecaServico::all()->keyBy('nome')->all();
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

    /** The fictitious company shown in the README screenshots (a real install keeps AdminSeeder's defaults). */
    private function seedEmpresaDemo(): void
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
     * @param  array<string, Cliente>  $clientes
     * @param  array<string, BibliotecaServico>  $biblioteca
     * @return array<string, Servico> keyed by the data.js id (s1..s11)
     */
    private function seedServicos(array $clientes, array $biblioteca): array
    {
        $linhas = [
            ['id' => 's1', 'clienteId' => 'c1', 'bib' => 'Seguro', 'descricao' => 'Seguro multirriscos do escritório, renovação anual.', 'nome' => 'Seguro do escritório', 'plano' => 'Multirriscos', 'categoria' => 'seguros', 'valor' => 12000, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '02/09/2025', 'vencimento' => '02/09/2026', 'status' => 'a_vencer', 'dias' => 3],
            ['id' => 's2', 'clienteId' => 'c1', 'bib' => 'Domínio .co.mz', 'descricao' => 'Registo e renovação do domínio da empresa.', 'nome' => 'silvaconsultoria.co.mz', 'plano' => 'Domínio .co.mz', 'categoria' => 'dominio', 'valor' => 800, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '02/09/2025', 'vencimento' => '02/09/2027', 'status' => 'activo', 'dias' => 368],
            ['id' => 's3', 'clienteId' => 'c1', 'bib' => 'Contabilidade', 'descricao' => 'Avença mensal: contabilidade, salários e declarações fiscais.', 'nome' => 'Contabilidade', 'plano' => 'Avença mensal', 'categoria' => 'financas', 'valor' => 4500, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '01/09/2026', 'vencimento' => '01/10/2026', 'status' => 'activo', 'dias' => 32],
            ['id' => 's4', 'clienteId' => 'c2', 'bib' => 'Explicações', 'descricao' => 'Explicações de Matemática, duas vezes por semana.', 'nome' => 'Explicações de Matemática', 'plano' => '2x por semana', 'categoria' => 'educacao', 'valor' => 1500, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '05/08/2026', 'vencimento' => '05/09/2026', 'status' => 'a_vencer', 'dias' => 6],
            ['id' => 's5', 'clienteId' => 'c2', 'bib' => 'Mensalidade', 'descricao' => 'Inscrição anual no ginásio, acesso livre.', 'nome' => 'Ginásio', 'plano' => 'Plano anual', 'categoria' => 'saude', 'valor' => 18000, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '20/10/2025', 'vencimento' => '20/10/2026', 'status' => 'activo', 'dias' => 51],
            ['id' => 's6', 'clienteId' => 'c3', 'bib' => 'Manutenção', 'descricao' => 'Quatro visitas por ano aos aparelhos de ar condicionado.', 'nome' => 'Manutenção de ar condicionado', 'plano' => '4 visitas / ano', 'categoria' => 'manutencao', 'valor' => 7200, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '08/09/2024', 'vencimento' => '08/09/2026', 'status' => 'a_vencer', 'dias' => 9],
            ['id' => 's7', 'clienteId' => 'c3', 'bib' => 'Limpeza', 'descricao' => 'Limpeza semanal do escritório, materiais incluídos.', 'nome' => 'Limpeza do escritório', 'plano' => 'Semanal', 'categoria' => 'manutencao', 'valor' => 6000, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '01/08/2026', 'vencimento' => '01/09/2026', 'status' => 'vencido', 'dias' => -1],
            ['id' => 's8', 'clienteId' => 'c4', 'bib' => 'Segurança e vigilância', 'descricao' => 'Guarda nocturno e monitorização do alarme.', 'nome' => 'Vigilância 24h', 'plano' => 'Guarda + alarme', 'categoria' => 'seguranca', 'valor' => 8500, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '15/08/2026', 'vencimento' => '15/09/2026', 'status' => 'activo', 'dias' => 16],
            ['id' => 's9', 'clienteId' => 'c4', 'bib' => 'Renda', 'descricao' => 'Arrendamento do armazém, pago anualmente.', 'nome' => 'Renda do armazém', 'plano' => 'Contrato anual', 'categoria' => 'imoveis', 'valor' => 96000, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '09/09/2025', 'vencimento' => '09/09/2026', 'status' => 'a_vencer', 'dias' => 10],
            ['id' => 's10', 'clienteId' => 'c5', 'bib' => 'Internet', 'descricao' => 'Plano de internet por fibra; suspenso por falta de pagamento.', 'nome' => 'Internet fibra', 'plano' => '100 Mbps', 'categoria' => 'telefonia', 'valor' => 1800, 'periodo' => 'mes', 'periodicidade' => 'mensal', 'inicio' => '05/07/2026', 'vencimento' => '05/08/2026', 'status' => 'suspenso', 'dias' => -25],
            ['id' => 's11', 'clienteId' => 'c2', 'bib' => 'Alojamento de site', 'descricao' => 'Alojamento do site da loja online.', 'nome' => 'Alojamento do site', 'plano' => 'Plano Start', 'categoria' => 'hospedagem', 'valor' => 900, 'periodo' => 'ano', 'periodicidade' => 'anual', 'inicio' => '28/08/2026', 'vencimento' => '28/08/2027', 'status' => 'activo', 'dias' => 363],
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
     * @param  array<string, Cliente>  $clientes
     * @param  array<string, Servico>  $servicos
     */
    private function seedPagamentos(array $clientes, array $servicos): void
    {
        $linhas = [
            ['data' => '30/08/2026', 'clienteId' => 'c2', 'servicoId' => 's11', 'valor' => 900, 'metodo' => 'mpesa', 'periodo' => '1 ano'],
            ['data' => '28/08/2026', 'clienteId' => 'c2', 'servicoId' => 's4', 'valor' => 1500, 'metodo' => 'transferencia', 'periodo' => '1 mês'],
            ['data' => '20/08/2026', 'clienteId' => 'c4', 'servicoId' => 's8', 'valor' => 8500, 'metodo' => 'emola', 'periodo' => '1 mês'],
            ['data' => '15/08/2026', 'clienteId' => 'c1', 'servicoId' => 's3', 'valor' => 4500, 'metodo' => 'mpesa', 'periodo' => '1 mês'],
            ['data' => '12/08/2026', 'clienteId' => 'c3', 'servicoId' => 's7', 'valor' => 6000, 'metodo' => 'dinheiro', 'periodo' => '1 mês'],
            ['data' => '04/08/2026', 'clienteId' => 'c4', 'servicoId' => 's9', 'valor' => 96000, 'metodo' => 'transferencia', 'periodo' => '1 ano'],
            ['data' => '18/08/2026', 'clienteId' => 'c1', 'servicoId' => 's1', 'valor' => 12000, 'metodo' => 'mpesa', 'periodo' => '1 ano'],
            ['data' => '14/08/2026', 'clienteId' => 'c2', 'servicoId' => 's5', 'valor' => 18000, 'metodo' => 'emola', 'periodo' => '1 ano'],
            ['data' => '11/08/2026', 'clienteId' => 'c3', 'servicoId' => 's6', 'valor' => 7200, 'metodo' => 'transferencia', 'periodo' => '1 ano'],
            ['data' => '08/08/2026', 'clienteId' => 'c4', 'servicoId' => 's8', 'valor' => 8500, 'metodo' => 'mpesa', 'periodo' => '1 mês'],
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
     * @param  array<string, Cliente>  $clientes
     * @param  array<string, Servico>  $servicos
     */
    private function seedHistoricoExemplo(array $clientes, array $servicos): void
    {
        // Ported verbatim from data.js's `historico` array, which narrates s1's
        // (Seguro do escritório, cliente c1) life from creation to its most recent reminders.
        $linhas = [
            ['data' => '01/09/2025', 'title' => 'Serviço criado', 'description' => 'Seguro do escritório — Multirriscos', 'kind' => 'criado'],
            ['data' => '02/09/2025', 'title' => 'Pagamento recebido', 'description' => '12.000 MZN — M-Pesa · 1 ano', 'kind' => 'pagamento'],
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
