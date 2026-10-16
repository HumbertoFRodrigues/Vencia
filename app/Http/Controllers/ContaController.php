<?php

namespace App\Http\Controllers;

use App\Enums\Papel;
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
    public const MAX_CONTAS_POR_DONO = 3;

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

    public function termos(): View
    {
        return $this->legal('termos', 'Termos de utilização');
    }

    public function privacidade(): View
    {
        return $this->legal('privacidade', 'Política de privacidade');
    }

    private function legal(string $pagina, string $titulo): View
    {
        return view('legal', [
            'pagina' => $pagina,
            'titulo' => $titulo,
            'actualizado' => '16/10/2026',
            'registoAberto' => RegistoController::opcoes()['aberto'],
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

        // Free contas are not a way around the plan limits: a person owns a few at most.
        $donoDe = $request->user()->contas()->wherePivot('papel', Papel::Dono->value)->count();
        if ($donoDe >= self::MAX_CONTAS_POR_DONO && ! $request->user()->super_admin) {
            return back()->withErrors(['nome' => 'Já és dono de '.$donoDe.' empresas, o máximo por pessoa. Para mais, contacta o suporte.'])->withInput();
        }

        app(PrepararConta::class)->criar($request->user(), $dados['nome']);

        return redirect()->route('dashboard')->with('toast', ['title' => 'Conta criada', 'body' => $dados['nome']]);
    }
}
