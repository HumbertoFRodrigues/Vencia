<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Clientes\ClienteDetail;
use App\Livewire\Clientes\ClientesIndex;
use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Livewire\Dashboard;
use App\Livewire\Financas\FinancasIndex;
use App\Livewire\Historico\HistoricoIndex;
use App\Livewire\Pagamentos\PagamentosIndex;
use App\Livewire\Servicos\ServicoDetail;
use App\Livewire\Servicos\ServicosIndex;
use App\Livewire\Vencimentos\VencimentosIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/clientes', ClientesIndex::class)->name('clientes.index');
    Route::get('/clientes/{cliente}', ClienteDetail::class)->name('clientes.show');

    Route::get('/servicos', ServicosIndex::class)->name('servicos.index');
    Route::get('/servicos/{servico}', ServicoDetail::class)->name('servicos.show');

    Route::get('/pagamentos', PagamentosIndex::class)->name('pagamentos.index');
    Route::get('/financas', FinancasIndex::class)->name('financas.index');

    Route::get('/vencimentos', VencimentosIndex::class)->name('vencimentos.index');
    Route::get('/historico', HistoricoIndex::class)->name('historico.index');
    Route::get('/configuracoes', ConfiguracoesIndex::class)->name('configuracoes.index');
});
