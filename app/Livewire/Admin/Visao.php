<?php

namespace App\Livewire\Admin;

use App\Models\Conta;
use App\Models\Fatura;
use App\Models\Plano;
use App\Models\User;
use App\Services\Faturacao;
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

    public function render()
    {
        return view('livewire.admin.visao', ['faturacao' => app(Faturacao::class)]);
    }
}
