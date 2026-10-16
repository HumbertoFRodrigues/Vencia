<?php

namespace Database\Seeders;

use App\Models\Plano;
use App\Models\User;
use App\Services\PrepararConta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * What a real installation needs and nothing more: the platform's
 * super-admin from ADMIN_EMAIL/ADMIN_PASSWORD, with their own conta (Pro, no
 * end date) holding the default settings, payment methods and service
 * library — no demo clients.
 *
 * Safe to run any number of times: it only creates what is missing and
 * never overwrites a password, a setting or a library entry already there
 * (production runs it from a cPanel cron that may fire more than once).
 * DatabaseSeeder calls it first and then adds the demo data on top.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (! $email || ! $password) {
            throw new RuntimeException('Defina ADMIN_EMAIL e ADMIN_PASSWORD no .env antes de criar a conta de administrador.');
        }

        $admin = User::query()->firstOrCreate(['email' => $email], [
            'name' => 'Administrador',
            'password' => $password, // the model's 'hashed' cast hashes this on save
            'email_verified_at' => Carbon::now(),
        ]);

        if (! $admin->super_admin) {
            $admin->forceFill(['super_admin' => true])->save();
        }

        $preparar = app(PrepararConta::class);
        $conta = $admin->contaActual();

        if ($conta) {
            $preparar->padroes($conta, $email);
        } else {
            $preparar->criar($admin, config('app.name', 'Vencia'), Plano::query()->where('slug', 'pro')->first());
        }
    }
}
