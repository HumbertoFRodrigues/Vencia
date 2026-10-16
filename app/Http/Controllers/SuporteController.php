<?php

namespace App\Http\Controllers;

use App\Models\ActividadeAdmin;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Entrar como": a super-admin sees the app exactly as a customer does, to
 * help them. A banner stays on screen the whole time, every visit is logged,
 * and "Voltar ao admin" restores the admin's own session.
 */
class SuporteController extends Controller
{
    public function entrarComo(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin->super_admin && ! $user->is($admin), 403);

        // Not another super-admin (their actions would look like theirs), and not a blocked user
        // (the block would sign the admin out too and lose the way back).
        if ($user->super_admin || $user->bloqueado()) {
            return back()->with('toast', ['title' => 'Não é possível entrar como este utilizador', 'body' => $user->super_admin ? 'É super-admin.' : 'Está bloqueado. Desbloqueia-o primeiro.', 'tone' => 'warning']);
        }

        ActividadeAdmin::registar('entrar_como', 'user:'.$user->id, $user->email);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('admin_original_id', $admin->id);

        return redirect()->route('dashboard');
    }

    public function voltar(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('admin_original_id');
        $admin = $adminId ? User::query()->where('super_admin', true)->find($adminId) : null;

        if (! $admin) {
            return redirect()->route('dashboard');
        }

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.utilizadores');
    }
}
