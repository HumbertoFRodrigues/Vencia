<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PlataformaMail;
use App\Models\Configuracao;
use App\Models\Convite;
use App\Models\Plano;
use App\Models\User;
use App\Services\Equipa;
use App\Services\PrepararConta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

/**
 * Public sign-up. A new person gets a user, their own conta (as dono) and
 * the free plan, or a Pro trial when the platform offers one. With an
 * invite link they join that conta instead and get no conta of their own.
 */
class RegistoController extends Controller
{
    /** @return array{aberto: bool, dias_teste: int} */
    public static function opcoes(): array
    {
        $registo = Configuracao::plataforma('registo', []);

        return [
            'aberto' => (bool) ($registo['aberto'] ?? true),
            'dias_teste' => max(0, (int) ($registo['dias_teste'] ?? 14)),
        ];
    }

    /** The super-admins hear about each new business (can be turned off in Admin → Plataforma). */
    private function avisarAdmins(User $user): void
    {
        $admins = User::query()->where('super_admin', true)->pluck('email')->all();
        if ($admins === [] || ! (Configuracao::plataforma('registo', [])['avisar_admin'] ?? true)) {
            return;
        }

        $conta = $user->contaActual();

        try {
            Configuracao::aplicarSmtpDaPlataforma();
            Mail::to($admins)->send(new PlataformaMail('Nova conta: '.($conta?->nome ?? $user->name), [
                $user->name.' ('.$user->email.') acabou de criar a conta "'.($conta?->nome ?? '—').'"'
                    .($conta?->em_teste ? ', em período de teste do '.$conta->planoEfectivo()->nome.' até '.$conta->plano_ate?->format('d/m/Y') : '').'.',
            ], $conta ? route('admin.contas.show', $conta) : route('admin.contas')));
        } catch (Throwable $e) {
            Log::warning('Aviso de nova conta não enviado: '.$e->getMessage());
        }
    }

    public function create(Request $request): View
    {
        $token = (string) $request->query('convite', '');
        $convite = $token !== '' ? Convite::porToken($token) : null;

        return view('auth.registar', [
            'aberto' => self::opcoes()['aberto'],
            'diasTeste' => self::opcoes()['dias_teste'],
            'convite' => $convite?->valido() ? $convite->load('conta') : null,
            'token' => $token,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: a person never fills it in.
        if ($request->filled('website')) {
            return redirect()->route('inicio');
        }

        $convite = $request->filled('convite') ? Convite::porToken((string) $request->input('convite')) : null;
        if ($convite && ! $convite->valido()) {
            return back()->withErrors(['email' => 'Este convite já expirou ou já foi usado. Pede um novo a quem te convidou.'])->onlyInput('name', 'email');
        }

        if ($convite && ! app(Equipa::class)->cabe($convite)) {
            return back()->withErrors(['email' => 'A equipa de '.$convite->conta->nome.' já está completa no plano actual. Pede a quem te convidou para passar ao Pro ou libertar um lugar.'])->onlyInput('name', 'email');
        }

        if (! $convite && ! self::opcoes()['aberto']) {
            return back()->withErrors(['email' => 'Os registos estão fechados de momento.']);
        }

        $dados = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'empresa' => ['nullable', 'string', 'max:120'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Já existe uma conta com este email. Entra ou recupera a palavra-passe.',
        ], [
            'name' => 'nome', 'empresa' => 'nome da empresa', 'password' => 'palavra-passe',
        ]);

        if ($convite && strcasecmp($convite->email, $dados['email']) !== 0) {
            return back()->withErrors(['email' => 'Este convite foi enviado para '.$convite->email.'.'])->onlyInput('name');
        }

        $user = DB::transaction(function () use ($dados, $convite): User {
            $user = User::create(['name' => $dados['name'], 'email' => $dados['email'], 'password' => $dados['password']]);

            if ($convite) {
                // The invite link proves the email address.
                $user->forceFill(['email_verified_at' => Carbon::now()])->save();
                app(Equipa::class)->aceitar($convite, $user);

                return $user;
            }

            $dias = self::opcoes()['dias_teste'];
            $pro = Plano::query()->where('slug', 'pro')->where('activo', true)->first();
            $nome = trim((string) ($dados['empresa'] ?? '')) ?: $dados['name'];

            $dias > 0 && $pro
                ? app(PrepararConta::class)->criar($user, $nome, $pro, Carbon::today()->addDays($dias), emTeste: true)
                : app(PrepararConta::class)->criar($user, $nome);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('toast', ['title' => 'Bem-vindo à equipa', 'body' => $convite->conta->nome]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            Log::error('Falha ao enviar o email de confirmação a '.$user->email.': '.$e->getMessage());
        }

        $this->avisarAdmins($user);

        return redirect()->route('verification.notice');
    }
}
