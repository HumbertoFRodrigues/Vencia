<?php

use App\Http\Controllers\Auth\DoisPassosController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistoController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificacaoEmailController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\ConviteController;
use App\Http\Controllers\FaturaController;
use App\Http\Controllers\ImplantacaoController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\SuporteController;
use App\Http\Middleware\ActualizarEstadosDoDia;
use App\Http\Middleware\DefinirConta;
use App\Livewire\Admin\Actividade as AdminActividade;
use App\Livewire\Admin\ContaDetalhe as AdminContaDetalhe;
use App\Livewire\Admin\Contas as AdminContas;
use App\Livewire\Admin\Faturas as AdminFaturas;
use App\Livewire\Admin\Planos as AdminPlanos;
use App\Livewire\Admin\Plataforma as AdminPlataforma;
use App\Livewire\Admin\Utilizadores as AdminUtilizadores;
use App\Livewire\Admin\Visao as AdminVisao;
use App\Livewire\Clientes\ClienteDetail;
use App\Livewire\Clientes\ClientesIndex;
use App\Livewire\Configuracoes\ConfiguracoesIndex;
use App\Livewire\Conta\Equipa as ContaEquipa;
use App\Livewire\Conta\Perfil as ContaPerfil;
use App\Livewire\Conta\Plano as ContaPlano;
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

        return redirect('/painel');
    });
}

// Called by the GitHub deploy workflow after the FTP upload (see ImplantacaoController). No session, no CSRF: token in a header.
Route::post('/implantacao/concluir', [ImplantacaoController::class, 'concluir'])
    ->withoutMiddleware(PreventRequestForgery::class)
    ->middleware('throttle:5,1')
    ->name('implantacao.concluir');

// Public home (logged-in people go straight to their panel).
Route::get('/', [ContaController::class, 'inicio'])->name('inicio');
Route::get('/termos', [ContaController::class, 'termos'])->name('termos');
Route::get('/privacidade', [ContaController::class, 'privacidade'])->name('privacidade');

// Team invites: anyone with the link can see it; accepting needs the invited account.
Route::get('/convite/{token}', [ConviteController::class, 'mostrar'])->name('convite.mostrar');
Route::post('/convite/{token}', [ConviteController::class, 'aceitar'])->middleware('auth')->name('convite.aceitar');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/login/dois-passos', [DoisPassosController::class, 'create'])->name('login.dois-passos');
    Route::post('/login/dois-passos', [DoisPassosController::class, 'store'])->middleware('throttle:login')->name('login.dois-passos.store');

    Route::get('/registar', [RegistoController::class, 'create'])->name('registar');
    Route::post('/registar', [RegistoController::class, 'store'])->middleware('throttle:registo')->name('registar.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:login')->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:login')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/confirmar-email', [VerificacaoEmailController::class, 'aviso'])->name('verification.notice');
    Route::get('/confirmar-email/{id}/{hash}', [VerificacaoEmailController::class, 'verificar'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/confirmar-email/reenviar', [VerificacaoEmailController::class, 'reenviar'])->middleware('throttle:3,1')->name('verification.send');

    // End "entrar como": reachable by the customer account the admin is using.
    Route::post('/suporte/voltar', [SuporteController::class, 'voltar'])->name('suporte.voltar');
});

Route::middleware(['auth', 'verified', DefinirConta::class])->group(function () {
    Route::get('/conta/nova', [ContaController::class, 'criar'])->name('conta.criar');
    Route::post('/conta/nova', [ContaController::class, 'guardar'])->middleware('throttle:5,1')->name('conta.guardar');
    Route::get('/conta/escolher', [ContaController::class, 'escolher'])->name('conta.escolher');
    Route::post('/conta/mudar', [ContaController::class, 'mudar'])->name('conta.mudar');

    Route::get('/faturas/{fatura}/comprovativo', [FaturaController::class, 'comprovativo'])->name('faturas.comprovativo');
    Route::get('/faturas/{fatura}/recibo', [FaturaController::class, 'recibo'])->name('faturas.recibo');
});

Route::middleware(['auth', 'verified', DefinirConta::class, ActualizarEstadosDoDia::class])->group(function () {
    Route::get('/painel', Dashboard::class)->name('dashboard');

    Route::get('/clientes', ClientesIndex::class)->name('clientes.index');
    Route::get('/clientes/{cliente}', ClienteDetail::class)->name('clientes.show');

    Route::get('/servicos', ServicosIndex::class)->name('servicos.index');
    Route::get('/servicos/{servico}', ServicoDetail::class)->name('servicos.show');

    Route::get('/pagamentos', PagamentosIndex::class)->name('pagamentos.index');
    Route::get('/pagamentos/{pagamento}/recibo', [ReciboController::class, 'pagamento'])->name('pagamentos.recibo');
    Route::get('/financas', FinancasIndex::class)->middleware('can:gerir-negocio')->name('financas.index');

    Route::get('/vencimentos', VencimentosIndex::class)->name('vencimentos.index');
    Route::get('/historico', HistoricoIndex::class)->middleware('can:gerir-negocio')->name('historico.index');
    Route::get('/configuracoes', ConfiguracoesIndex::class)->middleware('can:gerir-negocio')->name('configuracoes.index');

    Route::get('/conta/plano', ContaPlano::class)->middleware('can:gerir-conta')->name('conta.plano');
    Route::get('/conta/equipa', ContaEquipa::class)->middleware('can:gerir-conta')->name('conta.equipa');
    Route::get('/conta/perfil', ContaPerfil::class)->name('conta.perfil');

    Route::prefix('pessoal')->name('pessoal.')->group(function () {
        Route::get('/', PessoalResumo::class)->name('resumo');
        Route::get('/movimentos', PessoalMovimentos::class)->name('movimentos');
        Route::get('/fixos', PessoalFixos::class)->name('fixos');
        Route::get('/orcamentos', PessoalOrcamentos::class)->name('orcamentos');
        Route::get('/analise', PessoalAnalise::class)->name('analise');
        Route::get('/saldos', PessoalSaldos::class)->name('saldos');
    });
});

// The platform's own administration: super-admins only.
Route::middleware(['auth', 'verified', DefinirConta::class, 'can:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminVisao::class)->name('visao');
    Route::get('/contas', AdminContas::class)->name('contas');
    Route::get('/contas/{conta}', AdminContaDetalhe::class)->name('contas.show');
    Route::get('/utilizadores', AdminUtilizadores::class)->name('utilizadores');
    Route::get('/faturas', AdminFaturas::class)->name('faturas');
    Route::get('/planos', AdminPlanos::class)->name('planos');
    Route::get('/plataforma', AdminPlataforma::class)->name('plataforma');
    Route::get('/actividade', AdminActividade::class)->name('actividade');

    Route::post('/entrar-como/{user}', [SuporteController::class, 'entrarComo'])->name('entrar-como');
});
