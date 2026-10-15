<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RegistoController;
use App\Models\Configuracao;
use App\Models\Plano;
use App\Services\PrepararConta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public home page, plus choosing between contas for someone who
 * belongs to several, and starting a new one (also for a member whose only
 * conta was removed).
 */
class ContaController extends Controller
{
    public function inicio(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        return view('inicio', [
            'planos' => Plano::query()->where('activo', true)->orderBy('ordem')->get(),
            'diasTeste' => RegistoController::opcoes()['dias_teste'],
            'registoAberto' => RegistoController::opcoes()['aberto'],
            'moeda' => Configuracao::plataforma('faturacao', [])['moeda'] ?? 'MZN',
            'contacto' => Configuracao::plataforma('faturacao', [])['email'] ?? null,
        ]);
    }

    public function escolher(Request $request): View
    {
        return view('conta.escolher', [
            'contas' => $request->user()->contas()->orderBy('nome')->get(),
        ]);
    }

    public function mudar(Request $request): RedirectResponse
    {
        $conta = $request->user()->contas()->whereKey((int) $request->input('conta'))->firstOrFail();
        $request->user()->forceFill(['conta_actual_id' => $conta->id])->save();

        return redirect()->route('dashboard')->with('toast', ['title' => 'Conta: '.$conta->nome]);
    }

    public function criar(): View
    {
        return view('conta.criar');
    }

    public function guardar(Request $request): RedirectResponse
    {
        $dados = $request->validate(['nome' => ['required', 'string', 'max:120']], [], ['nome' => 'nome da empresa']);

        app(PrepararConta::class)->criar($request->user(), $dados['nome']);

        return redirect()->route('dashboard')->with('toast', ['title' => 'Conta criada', 'body' => $dados['nome']]);
    }
}
