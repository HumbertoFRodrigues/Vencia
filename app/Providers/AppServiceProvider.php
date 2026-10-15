<?php

namespace App\Providers;

use App\Contracts\GatewayPagamento;
use App\Enums\Papel;
use App\Http\Middleware\DefinirConta;
use App\Models\Fatura;
use App\Models\User;
use App\Services\FinancasPessoais;
use App\Services\Pagamentos\GatewayManual;
use App\Services\Totais;
use App\Support\ContaActual;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One per request (and per test): the conta never leaks from one request into the next.
        $this->app->scoped(ContaActual::class);

        // Plan invoices are paid by hand for now; an automatic gateway is a new binding here.
        $this->app->bind(GatewayPagamento::class, GatewayManual::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Links in emails (password reset, reminders) are built from APP_URL,
        // never from the request's Host header, and always https in production.
        if ($this->app->isProduction() && config('app.url')) {
            URL::forceRootUrl(config('app.url'));

            if (str_starts_with((string) config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }

        // Coarse per-IP backstop for the login/forgot-password/reset-password
        // forms (see routes/web.php's `throttle:login` middleware). The
        // precise per-email+IP throttle that actually produces the friendly
        // pt-PT message lives in LoginController — this is just a wider net
        // against raw request flooding from a single IP across many emails.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // Sign-ups: a handful per IP per hour is plenty for people, useless for bots.
        RateLimiter::for('registo', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });

        $this->definirPermissoes();

        // Livewire's update requests re-run these from the page's route, so a blocked user or a suspended conta is stopped there too.
        Livewire::addPersistentMiddleware([DefinirConta::class, EnsureEmailIsVerified::class]);

        // Sidebar nav items + "a vencer"/"a receber" badges, shared with every
        // page rendered through layouts.app. Kept as a lightweight composer
        // (not a Livewire component) so the shell never re-renders on inner
        // Livewire interactions.
        View::composer('layouts.app', function ($view): void {
            $painel = match (true) {
                request()->routeIs('pessoal.*') => 'pessoal',
                request()->routeIs('admin.*') => 'admin',
                default => 'empresa',
            };

            if ($painel === 'admin') {
                $definitions = [
                    ['id' => 'visao', 'label' => 'Visão geral', 'icon' => 'layout-dashboard', 'route' => 'admin.visao'],
                    ['id' => 'contas', 'label' => 'Contas', 'icon' => 'briefcase', 'route' => 'admin.contas'],
                    ['id' => 'utilizadores', 'label' => 'Utilizadores', 'icon' => 'users', 'route' => 'admin.utilizadores'],
                    ['id' => 'faturas', 'label' => 'Faturação', 'icon' => 'banknote', 'route' => 'admin.faturas', 'badge' => Fatura::query()->where('estado', Fatura::EM_ANALISE)->count()],
                    ['id' => 'planos', 'label' => 'Planos', 'icon' => 'package', 'route' => 'admin.planos'],
                    ['id' => 'plataforma', 'label' => 'Plataforma', 'icon' => 'settings', 'route' => 'admin.plataforma'],
                    ['id' => 'actividade', 'label' => 'Actividade', 'icon' => 'history', 'route' => 'admin.actividade'],
                ];
            } elseif ($painel === 'pessoal') {
                $definitions = [
                    ['id' => 'resumo', 'label' => 'Resumo', 'icon' => 'layout-dashboard', 'route' => 'pessoal.resumo', 'badge' => app(FinancasPessoais::class)->nAtrasoHoje()],
                    ['id' => 'movimentos', 'label' => 'Movimentos', 'icon' => 'list', 'route' => 'pessoal.movimentos'],
                    ['id' => 'fixos', 'label' => 'Fixos mensais', 'icon' => 'repeat', 'route' => 'pessoal.fixos'],
                    ['id' => 'orcamentos', 'label' => 'Orçamentos', 'icon' => 'target', 'route' => 'pessoal.orcamentos'],
                    ['id' => 'saldos', 'label' => 'Saldos e metas', 'icon' => 'piggy-bank', 'route' => 'pessoal.saldos'],
                    ['id' => 'analise', 'label' => 'Análise', 'icon' => 'chart-line', 'route' => 'pessoal.analise'],
                ];
            } else {
                $totais = app(Totais::class)->calcular();

                $definitions = [
                    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard'],
                    ['id' => 'clientes', 'label' => 'Clientes', 'icon' => 'users', 'route' => 'clientes.index'],
                    ['id' => 'servicos', 'label' => 'Serviços', 'icon' => 'package', 'route' => 'servicos.index'],
                    ['id' => 'vencimentos', 'label' => 'Vencimentos', 'icon' => 'calendar-clock', 'route' => 'vencimentos.index', 'badge' => $totais['aVencer']],
                    ['id' => 'pagamentos', 'label' => 'Pagamentos', 'icon' => 'banknote', 'route' => 'pagamentos.index', 'badge' => $totais['nAReceber']],
                    ['id' => 'financas', 'label' => 'Finanças', 'icon' => 'chart-line', 'route' => 'financas.index', 'can' => 'gerir-negocio'],
                    ['id' => 'historico', 'label' => 'Histórico', 'icon' => 'history', 'route' => 'historico.index', 'can' => 'gerir-negocio'],
                    ['id' => 'equipa', 'label' => 'Equipa', 'icon' => 'user-plus', 'route' => 'conta.equipa', 'can' => 'gerir-conta'],
                    ['id' => 'plano', 'label' => 'Plano', 'icon' => 'credit-card', 'route' => 'conta.plano', 'can' => 'gerir-conta'],
                    ['id' => 'configuracoes', 'label' => 'Configurações', 'icon' => 'settings', 'route' => 'configuracoes.index', 'can' => 'gerir-negocio'],
                ];
                $definitions = array_values(array_filter($definitions, fn (array $item) => ! isset($item['can']) || Gate::allows($item['can'])));
            }

            $navActive = null;
            $navItems = array_map(function (array $item) use (&$navActive): array {
                $item['href'] = Route::has($item['route']) ? route($item['route']) : '#';

                if (Route::has($item['route']) && request()->routeIs($item['route'])) {
                    $navActive = $item['id'];
                }

                return $item;
            }, $definitions);

            $view->with([
                'navItems' => $navItems,
                'navActive' => $navActive,
                'painel' => $painel,
            ]);
        });
    }

    /**
     * Who may do what inside a conta (see App\Enums\Papel), plus the
     * platform's super-admin. Used by routes (`can:`), Livewire actions and
     * the menu above.
     */
    private function definirPermissoes(): void
    {
        Gate::define('super-admin', fn (User $user) => $user->super_admin);
        Gate::define('gerir-conta', fn (User $user) => ContaActual::papel() === Papel::Dono);
        Gate::define('gerir-negocio', fn (User $user) => (bool) ContaActual::papel()?->podeGerirNegocio());
        Gate::define('retirar', fn (User $user) => ContaActual::papel() === Papel::Dono);
    }
}
