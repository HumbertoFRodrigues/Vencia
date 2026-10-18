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
use Illuminate\Support\Str;
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
        $linhas[] = $this->estadoUploads();
        $linhas[] = $this->ultimoErro();

        return $linhas;
    }

    /** Logos and payment proofs: what PHP needs for an upload to get through. */
    private function estadoUploads(): array
    {
        $falhas = [];
        if (! extension_loaded('fileinfo')) {
            $falhas[] = 'falta a extensão PHP "fileinfo" (cPanel → Select PHP Version → Extensions)';
        }
        if (! ini_get('file_uploads')) {
            $falhas[] = 'o PHP tem os uploads desligados (file_uploads)';
        }
        foreach ([storage_path('app/private') => 'storage/app/private', (string) config('filesystems.disks.public.root') => 'public/storage'] as $pasta => $nome) {
            if (! is_dir($pasta)) {
                @mkdir($pasta, 0755, true);
            }
            if (! is_writable($pasta)) {
                $falhas[] = 'a pasta '.$nome.' não tem escrita (permissão 755)';
            }
        }
        $temp = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
        if (! is_writable($temp)) {
            $falhas[] = 'a pasta temporária do PHP ('.$temp.') não tem escrita';
        }

        return [
            'ok' => $falhas === [],
            'titulo' => 'Carregar imagens (logótipos, comprovativos)',
            'detalhe' => $falhas === [] ? 'Pronto. Limite do PHP: '.ini_get('upload_max_filesize').'.' : 'Não funciona: '.implode('; ', $falhas).'.',
        ];
    }

    /** The newest ERROR in storage/logs/laravel.log, so a "500" can be explained without the File Manager. */
    private function ultimoErro(): array
    {
        $ficheiro = storage_path('logs/laravel.log');
        $erro = null;

        if (is_readable($ficheiro) && ($tamanho = filesize($ficheiro)) > 0) {
            $h = fopen($ficheiro, 'rb');
            fseek($h, max(0, $tamanho - 262144));
            $fim = (string) stream_get_contents($h);
            fclose($h);

            if (preg_match_all('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(?:ERROR|CRITICAL|ALERT|EMERGENCY): (.*)$/m', $fim, $m, PREG_SET_ORDER)) {
                $ultimo = end($m);
                $erro = ['em' => Carbon::parse($ultimo[1]), 'texto' => Str::limit(preg_replace('/\s*\{"(?:userId|exception)".*$/', '', $ultimo[2]), 300)];
            }
        }

        return [
            'ok' => ! $erro || $erro['em']->lt(Carbon::now()->subDay()),
            'titulo' => 'Último erro registado',
            'detalhe' => $erro ? $erro['em']->format('d/m/Y H:i').' · '.$erro['texto'] : 'Nenhum.',
        ];
    }

    public function render()
    {
        return view('livewire.admin.visao', ['faturacao' => app(Faturacao::class)]);
    }
}
