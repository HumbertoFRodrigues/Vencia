<?php

namespace App\Livewire\Admin;

use App\Models\Configuracao;
use App\Models\Conta;
use App\Models\Fatura;
use App\Models\Plano;
use App\Models\User;
use App\Services\CopiaSeguranca;
use App\Services\Faturacao;
use App\Support\Tarefas;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** The platform at a glance: contas, paying customers, this month's revenue, invoices waiting. */
#[Layout('layouts.app')]
#[Title('Administração')]
class Visao extends Component
{
    #[Computed]
    public function numeros(): array
    {
        $hoje = Carbon::today();
        $gratis = Plano::gratis()->id;

        $pagantes = Conta::query()->where('plano_id', '!=', $gratis)->where('em_teste', false)
            ->where(fn ($q) => $q->whereNull('plano_ate')->orWhere('plano_ate', '>=', $hoje));

        return [
            'contas' => Conta::query()->count(),
            'novas' => Conta::query()->where('created_at', '>=', $hoje->copy()->subDays(30))->count(),
            'pagantes' => (clone $pagantes)->count(),
            'teste' => Conta::query()->where('em_teste', true)->where('plano_ate', '>=', $hoje)->count(),
            'utilizadores' => User::query()->count(),
            'receitaMes' => (float) Fatura::query()->where('estado', Fatura::PAGA)->where('paga_em', '>=', $hoje->copy()->startOfMonth())->sum('valor'),
            'emAnalise' => Fatura::query()->where('estado', Fatura::EM_ANALISE)->count(),
        ];
    }

    #[Computed]
    public function recentes()
    {
        return Conta::query()->with('plano')->withCount('membros')->latest('id')->limit(8)->get();
    }

    #[Computed]
    public function porConfirmar()
    {
        return Fatura::query()->where('estado', Fatura::EM_ANALISE)->with('conta', 'plano')->oldest('enviada_em')->limit(6)->get();
    }

    /**
     * What a live install needs, checked: a red line here is something to fix on the server.
     *
     * @return list<array{ok: bool, titulo: string, detalhe: string}>
     */
    #[Computed]
    public function estado(): array
    {
        $smtp = Configuracao::plataforma('smtp', []);
        $pagamento = Configuracao::plataforma('faturacao', []);
        $copias = app(CopiaSeguranca::class)->existentes();
        $ultimaCopia = $copias[0]['data'] ?? null;

        $linhas = [
            [
                'ok' => filled($smtp['servidor'] ?? null),
                'titulo' => 'Envio de email',
                'detalhe' => filled($smtp['servidor'] ?? null) ? 'SMTP da plataforma: '.$smtp['servidor'] : 'Sem SMTP: confirmações de conta, convites e lembretes não saem. Admin → Plataforma.',
            ],
            [
                'ok' => filled($pagamento['mpesa'] ?? null) || filled($pagamento['emola'] ?? null) || filled($pagamento['banco'] ?? null),
                'titulo' => 'Dados de pagamento dos planos',
                'detalhe' => filled($pagamento['mpesa'] ?? null) || filled($pagamento['emola'] ?? null) || filled($pagamento['banco'] ?? null)
                    ? 'Os clientes vêem onde pagar o Pro.'
                    : 'Os clientes não sabem para onde enviar o pagamento. Admin → Plataforma.',
            ],
        ];

        foreach (Tarefas::estado() as $t) {
            $linhas[] = [
                'ok' => ! $t['atrasada'],
                'titulo' => 'Tarefa: '.$t['nome'],
                'detalhe' => $t['em']
                    ? 'Última vez '.$t['em']->diffForHumans().($t['resumo'] ? ' · '.$t['resumo'] : '').($t['atrasada'] ? '. Confirma o Cron Job.' : '')
                    : 'Nunca correu. Falta o Cron Job "'.$t['comando'].'" (DEPLOY.md, passo 7).',
            ];
        }

        $linhas[] = [
            'ok' => $ultimaCopia !== null && $ultimaCopia->gt(Carbon::now()->subDays(2)),
            'titulo' => 'Cópia de segurança',
            'detalhe' => $ultimaCopia ? 'Última: '.$ultimaCopia->format('d/m/Y H:i').' ('.count($copias).' guardadas)' : 'Ainda não há nenhuma.',
        ];
        $linhas[] = [
            'ok' => str_starts_with((string) config('app.url'), 'https://'),
            'titulo' => 'Endereço seguro (HTTPS)',
            'detalhe' => (string) config('app.url'),
        ];
        $linhas[] = [
            'ok' => ! (config('app.debug') && app()->isProduction()),
            'titulo' => 'Modo de depuração',
            'detalhe' => config('app.debug') ? (app()->isProduction() ? 'Ligado em produção: põe APP_DEBUG=false no .env.' : 'Ligado (normal em local).') : 'Desligado.',
        ];
        $linhas[] = [
            'ok' => is_writable(storage_path('logs')) && is_writable(storage_path('framework')),
            'titulo' => 'Pasta storage com escrita',
            'detalhe' => is_writable(storage_path('logs')) ? 'PHP '.PHP_VERSION.' · '.config('pagamentos.gateway').' (pagamento dos planos)' : 'O servidor não consegue escrever em storage/: dá permissão 755.',
        ];

        return $linhas;
    }

    public function render()
    {
        return view('livewire.admin.visao', ['faturacao' => app(Faturacao::class)]);
    }
}
