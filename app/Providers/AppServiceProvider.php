<?php

namespace App\Providers;

use App\Services\Totais;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
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
                'sidebarAVencer' => $totais['aVencer'],
                'sidebarAReceber' => $totais['nAReceber'],
            ]);
        });
    }
}
