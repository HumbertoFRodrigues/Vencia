<?php

namespace App\Livewire\Admin;

use App\Models\ActividadeAdmin;
use App\Models\Plano;
use App\Services\Faturacao;
use App\Support\Valor;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Plans: prices, limits (blank = no limit), features, order and whether new customers can pick them. */
#[Layout('layouts.app')]
#[Title('Planos')]
class Planos extends Component
{
    #[Locked]
    public ?int $editandoId = null;

    public bool $aberto = false;

    public string $nome = '';

    public string $descricao = '';

    public string $precoMensal = '0';

    public string $precoAnual = '0';

    /** @var array<string, string> */
    public array $limites = [];

    /** @var array<string, bool> */
    public array $funcoes = [];

    public bool $activo = true;

    public int $ordem = 1;

    public function novo(): void
    {
        $this->resetErrorBag();
        $this->editandoId = null;
        $this->nome = '';
        $this->descricao = '';
        $this->precoMensal = '0';
        $this->precoAnual = '0';
        $this->limites = array_fill_keys(array_keys(Plano::LIMITES), '');
        $this->funcoes = array_fill_keys(array_keys(Plano::FUNCOES), false);
        $this->activo = true;
        $this->ordem = (int) Plano::query()->max('ordem') + 1;
        $this->aberto = true;
    }

    public function editar(int $id): void
    {
        $plano = Plano::query()->findOrFail($id);
        $this->resetErrorBag();
        $this->editandoId = $plano->id;
        $this->nome = $plano->nome;
        $this->descricao = (string) $plano->descricao;
        $this->precoMensal = (string) (float) $plano->preco_mensal;
        $this->precoAnual = (string) (float) $plano->preco_anual;
        $this->limites = collect(Plano::LIMITES)->mapWithKeys(fn ($n, $k) => [$k => $plano->limite($k) === null ? '' : (string) $plano->limite($k)])->all();
        $this->funcoes = collect(Plano::FUNCOES)->mapWithKeys(fn ($n, $k) => [$k => $plano->permite($k)])->all();
        $this->activo = $plano->activo;
        $this->ordem = $plano->ordem;
        $this->aberto = true;
    }

    public function fechar(): void
    {
        $this->aberto = false;
    }

    public function guardar(): void
    {
        $this->precoMensal = Valor::normalizar($this->precoMensal);
        $this->precoAnual = Valor::normalizar($this->precoAnual);

        $this->validate([
            'nome' => ['required', 'string', 'max:60', Rule::unique('planos', 'nome')->ignore($this->editandoId)],
            'descricao' => ['nullable', 'string', 'max:300'],
            'precoMensal' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'precoAnual' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'limites.*' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'ordem' => ['required', 'integer', 'min:0', 'max:999'],
        ], [], ['precoMensal' => 'preço mensal', 'precoAnual' => 'preço anual', 'limites.*' => 'limite']);

        $plano = $this->editandoId ? Plano::query()->findOrFail($this->editandoId) : new Plano(['slug' => Str::slug($this->nome).'-'.Str::lower(Str::random(4))]);

        // The free plan is every conta's fallback: it stays free and available.
        $gratis = $plano->slug === 'gratis';

        $plano->fill([
            'nome' => $this->nome,
            'descricao' => $this->descricao ?: null,
            'preco_mensal' => $gratis ? 0 : (float) $this->precoMensal,
            'preco_anual' => $gratis ? 0 : (float) $this->precoAnual,
            'limites' => collect($this->limites)->map(fn ($v) => $v === '' || $v === null ? null : (int) $v)->all()
                + collect($this->funcoes)->map(fn ($v) => (bool) $v)->all(),
            'activo' => $gratis ? true : $this->activo,
            'ordem' => $this->ordem,
        ])->save();

        ActividadeAdmin::registar($this->editandoId ? 'plano.editado' : 'plano.criado', 'plano:'.$plano->id, $plano->nome);
        $this->aberto = false;
        $this->dispatch('toast', title: 'Plano guardado', body: $plano->nome, tone: 'success');
    }

    public function render()
    {
        return view('livewire.admin.planos', [
            'planos' => Plano::query()->withCount('contas')->orderBy('ordem')->get(),
            'moeda' => app(Faturacao::class)->moeda(),
        ]);
    }
}
