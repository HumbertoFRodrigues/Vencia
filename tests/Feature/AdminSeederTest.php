<?php

namespace Tests\Feature;

use App\Models\BibliotecaServico;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\User;
use App\Support\ContaActual;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_setup_creates_only_what_a_real_install_needs_and_can_rerun(): void
    {
        config(['app.admin_email' => 'admin@teste.mz', 'app.admin_password' => 'primeira']);

        $this->seed(AdminSeeder::class);
        $admin = User::sole();
        ContaActual::definir($admin->contaActual());
        Configuracao::where('chave', 'empresa')->first()->update(['valor' => ['nome' => 'Comusanas', 'email' => 'x@y.mz']]);

        // A second run (cron firing twice) must not reset the password or settings.
        config(['app.admin_password' => 'outra']);
        $this->seed(AdminSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('primeira', User::first()->password));
        $this->assertSame('Comusanas', Configuracao::obter('empresa')['nome']);
        $this->assertSame(13, BibliotecaServico::count());
        $this->assertSame(0, Cliente::count(), 'no demo data in production');
        $this->assertTrue($admin->fresh()->super_admin);
        $this->assertSame('pro', $admin->contaActual()->planoEfectivo()->slug);
        $this->assertSame(1, $admin->contas()->count(), 'a second run does not create a second conta');
    }
}
