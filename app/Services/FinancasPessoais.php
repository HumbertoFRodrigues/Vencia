<?php

namespace App\Services;

use App\Enums\TipoMovimento;
use App\Models\CategoriaPessoal;
use App\Models\Configuracao;
use App\Models\FixoPessoal;
use App\Models\MovimentoPessoal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Every number the personal panel shows comes from here, so Resumo, Fixos,
 * Orçamentos and Análise can never disagree with each other. All queries go
 * through the models' PertenceAoUtilizador scope (logged-in user only).
 */
class FinancasPessoais
{
    /** Pending fixos due within this many days are flagged "a vencer". */
    private const DIAS_PROXIMO = 3;

    private const CATEGORIAS_PADRAO = [
        'saida' => [
            ['Casa & Renda', 'house', 'indigo'],
            ['Alimentação', 'utensils', 'verde'],
            ['Transporte', 'car', 'azul'],
            ['Contas (água, luz, internet)', 'zap', 'ambar'],
            ['Saúde', 'heart-pulse', 'rosa'],
            ['Educação', 'graduation-cap', 'violeta'],
            ['Família', 'users', 'castanho'],
            ['Lazer', 'gamepad-2', 'teal'],
            ['Compras & Vestuário', 'shirt', 'aco'],
            ['Créditos & Dívidas', 'credit-card', 'vermelho'],
            ['Poupança & Investimento', 'piggy-bank', 'ciano'],
            ['Outros', 'package', 'cinza'],
        ],
        'entrada' => [
            ['Salário', 'briefcase', 'indigo'],
            ['Retirada da empresa', 'landmark', 'verde'],
            ['Trabalhos extra', 'hand-coins', 'teal'],
            ['Rendimentos & Investimentos', 'chart-line', 'ciano'],
            ['Outros', 'package', 'cinza'],
        ],
    ];

    /** First visit to the personal panel: give the user a sensible starting set of categories. */
    public function garantirCategoriasPadrao(): void
    {
        if (CategoriaPessoal::query()->exists()) {
            return;
        }

        foreach (self::CATEGORIAS_PADRAO as $tipo => $linhas) {
            foreach ($linhas as $ordem => [$nome, $icone, $cor]) {
                CategoriaPessoal::create([
                    'nome' => $nome, 'tipo' => $tipo, 'icone' => $icone, 'cor' => $cor, 'ordem' => $ordem,
                ]);
            }
        }
    }

    public static function mesDe(?string $valor): Carbon
    {
        if ($valor && preg_match('/^\d{4}-\d{2}$/', $valor)) {
            return Carbon::createFromFormat('!Y-m', $valor)->startOfMonth();
        }

        return Carbon::today()->startOfMonth();
    }

    public static function mzn(float $valor): string
    {
        return number_format(round($valor), 0, ',', '.').' '.Configuracao::moeda();
    }

    /**
     * The month's checklist of fixos: one row per fixo that applies to the
     * month (or that was confirmed/ignored in it even if since deactivated).
     *
     * @return Collection<int, array{fixo: FixoPessoal, estado: string, data: Carbon, valorPrevisto: float, movimento: ?MovimentoPessoal}>
     */
    public function fixosDoMes(Carbon $mes): Collection
    {
        $inicio = $mes->copy()->startOfMonth();
        $hoje = Carbon::today();

        $registos = MovimentoPessoal::query()
            ->whereNotNull('fixo_pessoal_id')
            ->whereDate('competencia', $inicio->toDateString())
            ->get()
            ->keyBy('fixo_pessoal_id');

        $fixos = FixoPessoal::query()
            ->with('categoria')
            ->where(function ($q) use ($inicio, $registos): void {
                $q->where(fn ($q2) => $q2->where('activo', true)->whereDate('inicio', '<=', $inicio->copy()->endOfMonth()->toDateString()))
                    ->orWhereIn('id', $registos->keys());
            })
            ->get();

        $ordem = ['atraso' => 0, 'proximo' => 1, 'previsto' => 2, 'confirmado' => 3, 'ignorado' => 4];

        return $fixos
            ->map(function (FixoPessoal $fixo) use ($inicio, $hoje, $registos): array {
                $data = $fixo->dataPrevista($inicio);
                $movimento = $registos->get($fixo->id);

                $estado = match (true) {
                    $movimento && $movimento->ignorado => 'ignorado',
                    (bool) $movimento => 'confirmado',
                    $data->lt($hoje) => 'atraso',
                    $hoje->diffInDays($data, false) <= self::DIAS_PROXIMO => 'proximo',
                    default => 'previsto',
                };

                return [
                    'fixo' => $fixo,
                    'estado' => $estado,
                    'data' => $data,
                    'valorPrevisto' => (float) $fixo->valor,
                    'movimento' => $movimento,
                ];
            })
            ->sortBy(fn (array $l) => sprintf('%d-%s', $ordem[$l['estado']], $l['data']->format('Ymd')))
            ->values();
    }

    /**
     * @return array{
     *     entradas: float, saidas: float, saldo: float,
     *     entradasPendentes: float, saidasPendentes: float, saldoProjectado: float,
     *     taxaPoupanca: ?float, taxaProjectada: ?float, saidasFixas: float, saidasVariaveis: float,
     *     nPendentes: int, nAtraso: int, nConfirmados: int, nFixos: int, nMovimentos: int,
     * }
     */
    public function resumoMes(Carbon $mes): array
    {
        $movimentos = MovimentoPessoal::query()->contabilizados()->noMes($mes)->get(['tipo', 'valor', 'fixo_pessoal_id']);

        $entradas = (float) $movimentos->where('tipo', TipoMovimento::Entrada)->sum('valor');
        $saidas = (float) $movimentos->where('tipo', TipoMovimento::Saida)->sum('valor');
        $saidasFixas = (float) $movimentos->where('tipo', TipoMovimento::Saida)->whereNotNull('fixo_pessoal_id')->sum('valor');

        $fixos = $this->fixosDoMes($mes);
        $pendentes = $fixos->whereIn('estado', ['atraso', 'proximo', 'previsto']);

        $entradasPendentes = (float) $pendentes->filter(fn ($l) => $l['fixo']->tipo === TipoMovimento::Entrada)->sum('valorPrevisto');
        $saidasPendentes = (float) $pendentes->filter(fn ($l) => $l['fixo']->tipo === TipoMovimento::Saida)->sum('valorPrevisto');

        $saldo = $entradas - $saidas;

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $saldo,
            'entradasPendentes' => $entradasPendentes,
            'saidasPendentes' => $saidasPendentes,
            'saldoProjectado' => ($entradas + $entradasPendentes) - ($saidas + $saidasPendentes),
            'taxaPoupanca' => $entradas > 0 ? ($saldo / $entradas) * 100 : null,
            'taxaProjectada' => ($entradas + $entradasPendentes) > 0
                ? ((($entradas + $entradasPendentes) - ($saidas + $saidasPendentes)) / ($entradas + $entradasPendentes)) * 100
                : null,
            'saidasFixas' => $saidasFixas,
            'saidasVariaveis' => $saidas - $saidasFixas,
            'nPendentes' => $pendentes->count(),
            'nAtraso' => $fixos->where('estado', 'atraso')->count(),
            'nConfirmados' => $fixos->where('estado', 'confirmado')->count(),
            'nFixos' => $fixos->count(),
            'nMovimentos' => $movimentos->count(),
        ];
    }

    /** One-click confirm with the expected value; dated today for the current month, else on the due day. */
    public function confirmarFixo(FixoPessoal $fixo, Carbon $mes): MovimentoPessoal
    {
        $mes = $mes->copy()->startOfMonth();
        $data = $mes->isSameMonth(Carbon::today()) ? Carbon::today() : $fixo->dataPrevista($mes);

        return MovimentoPessoal::updateOrCreate(
            ['fixo_pessoal_id' => $fixo->id, 'competencia' => $mes->toDateString()],
            [
                'tipo' => $fixo->tipo, 'descricao' => $fixo->descricao, 'categoria_pessoal_id' => $fixo->categoria_pessoal_id,
                'valor' => $fixo->valor, 'data' => $data->toDateString(), 'meio' => $fixo->meio, 'ignorado' => false,
            ],
        );
    }

    /** "Not this month" marker: keeps the fixo out of pending/overdue without counting any money. */
    public function ignorarFixo(FixoPessoal $fixo, Carbon $mes): void
    {
        $mes = $mes->copy()->startOfMonth();

        MovimentoPessoal::updateOrCreate(
            ['fixo_pessoal_id' => $fixo->id, 'competencia' => $mes->toDateString()],
            [
                'tipo' => $fixo->tipo, 'descricao' => $fixo->descricao, 'categoria_pessoal_id' => $fixo->categoria_pessoal_id,
                'valor' => $fixo->valor, 'data' => $fixo->dataPrevista($mes)->toDateString(), 'meio' => $fixo->meio, 'ignorado' => true,
            ],
        );
    }

    /** Undo a confirmation or an "ignorar": the fixo goes back to pending for that month. */
    public function reporFixo(FixoPessoal $fixo, Carbon $mes): void
    {
        MovimentoPessoal::query()
            ->where('fixo_pessoal_id', $fixo->id)
            ->whereDate('competencia', $mes->copy()->startOfMonth()->toDateString())
            ->delete();
    }

    /** Count of this month's overdue fixos — the sidebar badge. */
    public function nAtrasoHoje(): int
    {
        return $this->fixosDoMes(Carbon::today())->where('estado', 'atraso')->count();
    }

    /**
     * Totals per category for one month, biggest first; categories with nothing are left out.
     *
     * @return list<array{categoria: CategoriaPessoal, total: float, pct: float}>
     */
    public function porCategoria(Carbon $mes, TipoMovimento $tipo): array
    {
        $somas = MovimentoPessoal::query()->contabilizados()->noMes($mes)
            ->where('tipo', $tipo->value)
            ->selectRaw('categoria_pessoal_id, sum(valor) as total')
            ->groupBy('categoria_pessoal_id')
            ->pluck('total', 'categoria_pessoal_id');

        $total = (float) $somas->sum();
        $categorias = CategoriaPessoal::query()->whereIn('id', $somas->keys())->get()->keyBy('id');

        return $somas
            ->map(fn ($valor, $id) => [
                'categoria' => $categorias[$id],
                'total' => (float) $valor,
                'pct' => $total > 0 ? ((float) $valor / $total) * 100 : 0,
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Budget status for every saída category with a monthly limit: what was
     * really spent, plus what is still committed through unpaid fixos.
     *
     * @return list<array{categoria: CategoriaPessoal, gasto: float, pendente: float, limite: float, pct: float, pctPendente: float, estado: string}>
     */
    public function orcamentos(Carbon $mes): array
    {
        $categorias = CategoriaPessoal::query()->activas()->doTipo(TipoMovimento::Saida)
            ->whereNotNull('limite_mensal')->where('limite_mensal', '>', 0)
            ->orderBy('ordem')->orderBy('nome')->get();

        if ($categorias->isEmpty()) {
            return [];
        }

        $gastos = MovimentoPessoal::query()->contabilizados()->noMes($mes)
            ->where('tipo', TipoMovimento::Saida->value)
            ->whereIn('categoria_pessoal_id', $categorias->pluck('id'))
            ->selectRaw('categoria_pessoal_id, sum(valor) as total')
            ->groupBy('categoria_pessoal_id')
            ->pluck('total', 'categoria_pessoal_id');

        $pendentes = $this->fixosDoMes($mes)
            ->whereIn('estado', ['atraso', 'proximo', 'previsto'])
            ->filter(fn ($l) => $l['fixo']->tipo === TipoMovimento::Saida)
            ->groupBy(fn ($l) => $l['fixo']->categoria_pessoal_id)
            ->map(fn ($linhas) => (float) $linhas->sum('valorPrevisto'));

        return $categorias->map(function (CategoriaPessoal $c) use ($gastos, $pendentes): array {
            $limite = (float) $c->limite_mensal;
            $gasto = (float) ($gastos[$c->id] ?? 0);
            $pendente = (float) ($pendentes[$c->id] ?? 0);
            $pct = $limite > 0 ? ($gasto / $limite) * 100 : 0;
            $pctComprometido = $limite > 0 ? (($gasto + $pendente) / $limite) * 100 : 0;

            return [
                'categoria' => $c,
                'gasto' => $gasto,
                'pendente' => $pendente,
                'limite' => $limite,
                'pct' => $pct,
                'pctPendente' => max(0, min(100, $pctComprometido) - min(100, $pct)),
                'estado' => match (true) {
                    $pct > 100 => 'excedido',
                    $pct >= 80 || $pctComprometido > 100 => 'alerta',
                    default => 'ok',
                },
            ];
        })->all();
    }

    /**
     * Twelve months of one year, January first.
     *
     * @return list<array{mes: Carbon, label: string, entradas: float, saidas: float, saldo: float, taxa: ?float, futuro: bool}>
     */
    public function serieAnual(int $ano): array
    {
        $inicio = Carbon::create($ano, 1, 1)->startOfDay();
        $fim = $inicio->copy()->endOfYear();

        $linhas = MovimentoPessoal::query()->contabilizados()
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
            ->selectRaw('MONTH(data) as m, tipo, sum(valor) as total')
            ->groupByRaw('MONTH(data), tipo')
            ->get();

        $hoje = Carbon::today();
        $serie = [];

        for ($m = 1; $m <= 12; $m++) {
            $entradas = (float) $linhas->first(fn ($l) => (int) $l->m === $m && $l->tipo === TipoMovimento::Entrada)?->total;
            $saidas = (float) $linhas->first(fn ($l) => (int) $l->m === $m && $l->tipo === TipoMovimento::Saida)?->total;
            $mes = Carbon::create($ano, $m, 1);

            $serie[] = [
                'mes' => $mes,
                'label' => ucfirst($mes->locale('pt_PT')->translatedFormat('M')),
                'entradas' => $entradas,
                'saidas' => $saidas,
                'saldo' => $entradas - $saidas,
                'taxa' => $entradas > 0 ? (($entradas - $saidas) / $entradas) * 100 : null,
                'futuro' => $mes->gt($hoje),
            ];
        }

        return $serie;
    }

    /**
     * Year totals per category with the monthly average and the change versus the previous year.
     *
     * @return list<array{categoria: CategoriaPessoal, total: float, media: float, pct: float, anterior: float}>
     */
    public function categoriasDoAno(int $ano, TipoMovimento $tipo): array
    {
        $somar = function (int $a) use ($tipo) {
            return MovimentoPessoal::query()->contabilizados()
                ->where('tipo', $tipo->value)
                ->whereBetween('data', [Carbon::create($a, 1, 1)->toDateString(), Carbon::create($a, 12, 31)->toDateString()])
                ->selectRaw('categoria_pessoal_id, sum(valor) as total')
                ->groupBy('categoria_pessoal_id')
                ->pluck('total', 'categoria_pessoal_id');
        };

        $actual = $somar($ano);
        $anterior = $somar($ano - 1);
        $total = (float) $actual->sum();
        $meses = $this->mesesActivos($ano);
        $categorias = CategoriaPessoal::query()->whereIn('id', $actual->keys())->get()->keyBy('id');

        return $actual
            ->map(fn ($valor, $id) => [
                'categoria' => $categorias[$id],
                'total' => (float) $valor,
                'media' => (float) $valor / $meses,
                'pct' => $total > 0 ? ((float) $valor / $total) * 100 : 0,
                'anterior' => (float) ($anterior[$id] ?? 0),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Months of $ano the user has actually been tracking: from the first month
     * with a movimento up to the last month that has started. Averages divide
     * by this, so starting mid-year doesn't drag every "média / mês" down.
     */
    public function mesesActivos(int $ano): int
    {
        $primeiro = MovimentoPessoal::query()->contabilizados()
            ->whereBetween('data', [Carbon::create($ano, 1, 1)->toDateString(), Carbon::create($ano, 12, 31)->toDateString()])
            ->min('data');

        if (! $primeiro) {
            return 1;
        }

        $hoje = Carbon::today();
        $ultimo = $ano < $hoje->year ? 12 : ($ano > $hoje->year ? 1 : $hoje->month);

        return max(1, $ultimo - Carbon::parse($primeiro)->month + 1);
    }

    /**
     * Monthly averages over the last (up to) 3 complete months that have
     * data — falling back to the current month when the user is brand new.
     * Base for the "what if I cut / earn more" simulator.
     *
     * @return array{meses: int, entradas: float, saidas: float, categorias: array<int, float>}
     */
    public function mediasRecentes(): array
    {
        $hoje = Carbon::today();
        $candidatos = collect([1, 2, 3])->map(fn ($i) => $hoje->copy()->startOfMonth()->subMonthsNoOverflow($i));

        $comDados = $candidatos->filter(fn (Carbon $m) => MovimentoPessoal::query()->contabilizados()->noMes($m)->exists())->values();

        if ($comDados->isEmpty() && MovimentoPessoal::query()->contabilizados()->noMes($hoje)->exists()) {
            $comDados = collect([$hoje->copy()->startOfMonth()]);
        }

        if ($comDados->isEmpty()) {
            return ['meses' => 0, 'entradas' => 0.0, 'saidas' => 0.0, 'categorias' => []];
        }

        $n = $comDados->count();
        $movimentos = MovimentoPessoal::query()->contabilizados()
            ->where(function ($q) use ($comDados): void {
                foreach ($comDados as $m) {
                    $q->orWhereBetween('data', [$m->copy()->startOfMonth()->toDateString(), $m->copy()->endOfMonth()->toDateString()]);
                }
            })
            ->get(['tipo', 'valor', 'categoria_pessoal_id']);

        return [
            'meses' => $n,
            'entradas' => (float) $movimentos->where('tipo', TipoMovimento::Entrada)->sum('valor') / $n,
            'saidas' => (float) $movimentos->where('tipo', TipoMovimento::Saida)->sum('valor') / $n,
            'categorias' => $movimentos->where('tipo', TipoMovimento::Saida)
                ->groupBy('categoria_pessoal_id')
                ->map(fn ($linhas) => (float) $linhas->sum('valor') / $n)
                ->all(),
        ];
    }

    /**
     * Plain-language observations for the month, most urgent first. Rules,
     * not magic: every line states the numbers behind it.
     *
     * @return list<array{tom: string, icone: string, texto: string}>
     */
    public function insights(Carbon $mes): array
    {
        $r = $this->resumoMes($mes);
        $hoje = Carbon::today();
        $mesActual = $mes->isSameMonth($hoje);
        $insights = [];

        if ($r['nMovimentos'] === 0 && $r['nFixos'] === 0) {
            return [[
                'tom' => 'info', 'icone' => 'info',
                'texto' => 'Ainda não há movimentos neste mês. Começa por registar as tuas entradas e débitos fixos — o resto passa a ser automático.',
            ]];
        }

        if ($r['nAtraso'] > 0) {
            $insights[] = ['tom' => 'perigo', 'icone' => 'circle-alert', 'texto' => sprintf(
                '%d %s já %s o dia previsto sem estar confirmado.',
                $r['nAtraso'], $r['nAtraso'] === 1 ? 'fixo' : 'fixos', $r['nAtraso'] === 1 ? 'passou' : 'passaram',
            )];
        }

        // Judge the month by where it is heading, not by mid-month timing (rent paid on the 5th, salary on the 25th).
        if ($r['saldoProjectado'] < 0) {
            $insights[] = ['tom' => 'perigo', 'icone' => 'triangle-alert', 'texto' => $r['nPendentes'] > 0
                ? sprintf('Mesmo contando as entradas e débitos fixos que faltam, o mês fecha negativo em %s.', self::mzn(abs($r['saldoProjectado'])))
                : sprintf('Saiu mais do que entrou: o mês está negativo em %s.', self::mzn(abs($r['saldoProjectado']))),
            ];
        } elseif ($r['saldo'] < 0) {
            $insights[] = ['tom' => 'info', 'icone' => 'hourglass', 'texto' => sprintf(
                'Por agora saíram %s a mais do que entrou, mas com as entradas previstas o mês fecha positivo em %s.',
                self::mzn(abs($r['saldo'])), self::mzn($r['saldoProjectado']),
            )];
        }

        foreach ($this->orcamentos($mes) as $o) {
            $nome = $o['categoria']->nome;
            if ($o['estado'] === 'excedido') {
                $insights[] = ['tom' => 'perigo', 'icone' => 'target', 'texto' => sprintf(
                    'Passaste o limite de %s em %s (%s de %s).',
                    $nome, self::mzn($o['gasto'] - $o['limite']), self::mzn($o['gasto']), self::mzn($o['limite']),
                )];
            } elseif ($o['estado'] === 'alerta') {
                $faltam = $mesActual ? $hoje->diffInDays($hoje->copy()->endOfMonth()) : null;
                $insights[] = ['tom' => 'alerta', 'icone' => 'target', 'texto' => sprintf(
                    '%s já vai em %d%% do limite%s.',
                    $nome, round($o['pct'] + $o['pctPendente']), $faltam !== null ? sprintf(' e faltam %d dias para o fim do mês', $faltam) : '',
                )];
            }
        }

        // Category spending versus the average of the previous 3 months that had data.
        $anteriores = collect([1, 2, 3])->map(fn ($i) => $mes->copy()->startOfMonth()->subMonthsNoOverflow($i))
            ->filter(fn (Carbon $m) => MovimentoPessoal::query()->contabilizados()->noMes($m)->exists());

        if ($anteriores->isNotEmpty()) {
            $mediaAnterior = MovimentoPessoal::query()->contabilizados()
                ->where('tipo', TipoMovimento::Saida->value)
                ->where(function ($q) use ($anteriores): void {
                    foreach ($anteriores as $m) {
                        $q->orWhereBetween('data', [$m->toDateString(), $m->copy()->endOfMonth()->toDateString()]);
                    }
                })
                ->selectRaw('categoria_pessoal_id, sum(valor) as total')
                ->groupBy('categoria_pessoal_id')
                ->pluck('total', 'categoria_pessoal_id')
                ->map(fn ($v) => (float) $v / $anteriores->count());

            $minimo = max(500, $r['entradas'] * 0.03);
            $maiorSubida = collect($this->porCategoria($mes, TipoMovimento::Saida))
                ->map(fn ($l) => $l + ['media' => (float) ($mediaAnterior[$l['categoria']->id] ?? 0)])
                ->filter(fn ($l) => $l['media'] > 0 && $l['total'] - $l['media'] >= $minimo && $l['total'] >= $l['media'] * 1.25)
                ->sortByDesc(fn ($l) => $l['total'] - $l['media'])
                ->first();

            if ($maiorSubida) {
                $insights[] = ['tom' => 'alerta', 'icone' => 'arrow-up', 'texto' => sprintf(
                    '%s: %s este mês contra uma média de %s nos meses anteriores (+%d%%).',
                    $maiorSubida['categoria']->nome, self::mzn($maiorSubida['total']), self::mzn($maiorSubida['media']),
                    round((($maiorSubida['total'] - $maiorSubida['media']) / $maiorSubida['media']) * 100),
                )];
            }
        }

        $fixosEntrada = (float) FixoPessoal::query()->where('activo', true)->where('tipo', TipoMovimento::Entrada->value)->sum('valor');
        $fixosSaida = (float) FixoPessoal::query()->where('activo', true)->where('tipo', TipoMovimento::Saida->value)->sum('valor');

        if ($fixosEntrada > 0 && $fixosSaida > 0) {
            $peso = ($fixosSaida / $fixosEntrada) * 100;
            if ($peso >= 70) {
                $insights[] = ['tom' => 'alerta', 'icone' => 'repeat', 'texto' => sprintf(
                    'Os débitos fixos levam %d%% da tua renda fixa — sobra pouca margem para imprevistos (o ideal é abaixo de 50%%).', round($peso),
                )];
            } elseif ($peso <= 50) {
                $insights[] = ['tom' => 'bom', 'icone' => 'shield-check', 'texto' => sprintf(
                    'Os débitos fixos levam %d%% da tua renda fixa — margem saudável.', round($peso),
                )];
            }
        }

        if ($mesActual && $hoje->day >= 5 && $r['saidasVariaveis'] > 0) {
            $projeccao = $r['saidasVariaveis'] / $hoje->day * $hoje->daysInMonth;
            $insights[] = ['tom' => 'info', 'icone' => 'chart-line', 'texto' => sprintf(
                'Ao ritmo actual, as despesas do dia-a-dia (fora dos fixos) fecham o mês perto de %s.', self::mzn($projeccao),
            )];
        }

        if ($r['taxaPoupanca'] !== null && $r['saldo'] >= 0) {
            if ($r['taxaPoupanca'] >= 20) {
                $insights[] = ['tom' => 'bom', 'icone' => 'piggy-bank', 'texto' => sprintf(
                    'Estás a guardar %d%% do que entrou este mês — acima da referência de 20%%.', round($r['taxaPoupanca']),
                )];
            } else {
                $falta = ($r['entradas'] * 0.20) - $r['saldo'];
                $insights[] = ['tom' => 'info', 'icone' => 'piggy-bank', 'texto' => sprintf(
                    'Estás a guardar %d%% do que entrou. Para chegar aos 20%% faltam %s este mês.', round($r['taxaPoupanca']), self::mzn($falta),
                )];
            }
        }

        $peso = ['perigo' => 0, 'alerta' => 1, 'info' => 2, 'bom' => 3];
        usort($insights, fn ($a, $b) => $peso[$a['tom']] <=> $peso[$b['tom']]);

        return array_slice($insights, 0, 6);
    }
}
