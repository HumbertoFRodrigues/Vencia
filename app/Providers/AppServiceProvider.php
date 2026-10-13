<?php

namespace App\Providers;

use App\Services\FinancasPessoais;
use App\Services\Totais;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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

        // Sidebar nav items + "a vencer"/"a receber" badges, shared with every
        // page rendered through layouts.app. Kept as a lightweight composer
        // (not a Livewire component) so the shell never re-renders on inner
        // Livewire interactions.
        View::composer('layouts.app', function ($view): void {
            $painel = request()->routeIs('pessoal.*') ? 'pessoal' : 'empresa';

            if ($painel === 'pessoal') {
                $definitions = [
                    ['id' => 'resumo', 'label' => 'Resumo', 'icon' => 'layout-dashboard', 'route' => 'pessoal.resumo', 'badge' => app(FinancasPessoais::class)->nAtrasoHoje()],
                    ['id' => 'movimentos', 'label' => 'Movimentos', 'icon' => 'list', 'route' => 'pessoal.movimentos'],
                    ['id' => 'fixos', 'label' => 'Fixos mensais', 'icon' => 'repeat', 'route' => 'pessoal.fixos'],
                    ['id' => 'orcamentos', 'label' => 'Orçamentos', 'icon' => 'target', 'route' => 'pessoal.orcamentos'],
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
                    ['id' => 'financas', 'label' => 'Finanças', 'icon' => 'chart-line', 'route' => 'financas.index'],
                    ['id' => 'historico', 'label' => 'Histórico', 'icon' => 'history', 'route' => 'historico.index'],
                    ['id' => 'configuracoes', 'label' => 'Configurações', 'icon' => 'settings', 'route' => 'configuracoes.index'],
                ];
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
}
