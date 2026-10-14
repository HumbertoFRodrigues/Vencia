<?php

use App\Http\Controllers\Auth\DoisPassosController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ImplantacaoController;
use App\Http\Controllers\ReciboController;
use App\Http\Middleware\ActualizarEstadosDoDia;
use App\Livewire\Clientes\ClienteDetail;
use App\Livewire\Clientes\ClientesIndex;
use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Livewire\Dashboard;
use App\Livewire\Financas\FinancasIndex;
use App\Livewire\Historico\HistoricoIndex;
use App\Livewire\Pagamentos\PagamentosIndex;
use App\Livewire\Pessoal\Analise as PessoalAnalise;
use App\Livewire\Pessoal\Fixos as PessoalFixos;
use App\Livewire\Pessoal\Movimentos as PessoalMovimentos;
use App\Livewire\Pessoal\Orcamentos as PessoalOrcamentos;
use App\Livewire\Pessoal\Resumo as PessoalResumo;
use App\Livewire\Pessoal\Saldos as PessoalSaldos;
use App\Livewire\Servicos\ServicoDetail;
use App\Livewire\Servicos\ServicosIndex;
use App\Livewire\Vencimentos\VencimentosIndex;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

if (app()->environment('local')) {
    Route::get('/__test-login', function () {
        auth()->login(User::first());

        return redirect('/');
    });
}

// Called by the GitHub deploy workflow after the FTP upload (see ImplantacaoController). No session, no CSRF: token in a header.
Route::post('/implantacao/concluir', [ImplantacaoController::class, 'concluir'])
    ->withoutMiddleware(PreventRequestForgery::class)
    ->middleware('throttle:5,1')
    ->name('implantacao.concluir');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/login/dois-passos', [DoisPassosController::class, 'create'])->name('login.dois-passos');
    Route::post('/login/dois-passos', [DoisPassosController::class, 'store'])->middleware('throttle:login')->name('login.dois-passos.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:login')->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:login')->name('password.update');
});

Route::middleware(['auth', ActualizarEstadosDoDia::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/clientes', ClientesIndex::class)->name('clientes.index');
    Route::get('/clientes/{cliente}', ClienteDetail::class)->name('clientes.show');

    Route::get('/servicos', ServicosIndex::class)->name('servicos.index');
    Route::get('/servicos/{servico}', ServicoDetail::class)->name('servicos.show');

    Route::get('/pagamentos', PagamentosIndex::class)->name('pagamentos.index');
    Route::get('/pagamentos/{pagamento}/recibo', [ReciboController::class, 'pagamento'])->name('pagamentos.recibo');
    Route::get('/financas', FinancasIndex::class)->name('financas.index');

    Route::get('/vencimentos', VencimentosIndex::class)->name('vencimentos.index');
    Route::get('/historico', HistoricoIndex::class)->name('historico.index');
    Route::get('/configuracoes', ConfiguracoesIndex::class)->name('configuracoes.index');

    Route::prefix('pessoal')->name('pessoal.')->group(function () {
        Route::get('/', PessoalResumo::class)->name('resumo');
        Route::get('/movimentos', PessoalMovimentos::class)->name('movimentos');
        Route::get('/fixos', PessoalFixos::class)->name('fixos');
        Route::get('/orcamentos', PessoalOrcamentos::class)->name('orcamentos');
        Route::get('/analise', PessoalAnalise::class)->name('analise');
        Route::get('/saldos', PessoalSaldos::class)->name('saldos');
    });
});
