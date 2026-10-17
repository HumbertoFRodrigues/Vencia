<?php

namespace App\Livewire\Admin;

use App\Models\ActividadeAdmin;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/** Who did what in the administration, newest first. */
#[Layout('layouts.app')]
#[Title('Actividade')]
class Actividade extends Component
{
    use WithPagination;

    public const ACCOES = [
        'entrar_como' => 'Entrou como',
        'fatura.aprovada' => 'Pagamento confirmado',
        'fatura.rejeitada' => 'Pagamento rejeitado',
        'plano.expirou' => 'Plano terminou',
        'conta.plano' => 'Plano mudado à mão',
        'conta.suspensa' => 'Conta suspensa',
        'conta.reactivada' => 'Conta reactivada',
        'conta.apagada' => 'Conta apagada',
        'conta.nome' => 'Nome da conta mudado',
        'conta.dias_oferecidos' => 'Dias de Pro oferecidos',
        'conta.membro_papel' => 'Papel de membro mudado',
        'conta.membro_removido' => 'Membro removido',
        'conta.membro_adicionado' => 'Membro acrescentado',
        'user.editado' => 'Utilizador editado',
        'user.bloqueado' => 'Utilizador bloqueado',
        'user.desbloqueado' => 'Utilizador desbloqueado',
        'user.email_confirmado' => 'Email confirmado à mão',
        'user.reposicao_enviada' => 'Link de reposição enviado',
        'user.super_admin' => 'Tornado super-admin',
        'user.sem_super_admin' => 'Deixou de ser super-admin',
        'user.apagou_conta' => 'Utilizador apagou a conta',
        'plano.criado' => 'Plano criado',
        'plano.editado' => 'Plano editado',
        'plataforma.smtp' => 'SMTP da plataforma',
        'plataforma.faturacao' => 'Dados de pagamento',
        'plataforma.registo' => 'Opções de registo',
        'plataforma.copia' => 'Cópia de segurança',
    ];

    public function render()
    {
        return view('livewire.admin.actividade', [
            'registos' => ActividadeAdmin::query()->with('user')->latest('id')->paginate(40),
        ]);
    }
}
