<?php

namespace App\Livewire\Clientes;

use App\Enums\ClienteStatus;
use App\Models\Cliente;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Create/edit dialog for Cliente. Nested (not a full-page route) so it can be
 * dropped into any page via <livewire:clientes.cliente-form /> and opened
 * from there by dispatching the browser event 'cliente-form:abrir' (with an
 * optional clienteId payload for edit mode). On save it dispatches
 * `$savedEvent` (default 'cliente-guardado', overridable via the
 * saved-event="" attribute) with the cliente id, so any listening component —
 * ClientesIndex today, the Serviços "novo cliente" inline flow in Fase 4 —
 * can react without this component knowing who's listening.
 */
class ClienteForm extends Component
{
    public bool $show = false;

    public ?int $clienteId = null;

    public string $nome = '';

    public string $email = '';

    public ?string $tel = null;

    public ?string $empresa = null;

    public string $status = 'activo';

    public string $savedEvent = 'cliente-guardado';

    #[On('cliente-form:abrir')]
    public function abrir(?int $clienteId = null): void
    {
        $this->resetValidation();
        $this->clienteId = $clienteId;

        if ($clienteId !== null) {
            $cliente = Cliente::findOrFail($clienteId);
            $this->nome = $cliente->nome;
            $this->email = $cliente->email;
            $this->tel = $cliente->tel;
            $this->empresa = $cliente->empresa;
            $this->status = $cliente->status->value;
        } else {
            $this->reset(['nome', 'email', 'tel', 'empresa']);
            $this->status = ClienteStatus::Activo->value;
        }

        $this->show = true;
    }

    public function fechar(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('clientes', 'email')->ignore($this->clienteId),
            ],
            'tel' => ['nullable', 'string', 'max:50'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ClienteStatus::class)],
        ];
    }

    public function guardar(): void
    {
        $data = $this->validate();
        $data['tel'] = $data['tel'] !== null && trim($data['tel']) === '' ? null : $data['tel'];
        $data['empresa'] = $data['empresa'] !== null && trim($data['empresa']) === '' ? null : $data['empresa'];

        if ($this->clienteId !== null) {
            $cliente = Cliente::findOrFail($this->clienteId);
            $cliente->update($data);
        } else {
            $data['desde'] = Carbon::today()->toDateString();
            $cliente = Cliente::create($data);
        }

        $this->show = false;
        $this->dispatch($this->savedEvent, clienteId: $cliente->id);
    }

    public function render()
    {
        return view('livewire.clientes.cliente-form');
    }
}
