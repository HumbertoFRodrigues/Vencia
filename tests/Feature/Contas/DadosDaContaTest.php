<?php

namespace Tests\Feature\Contas;

use App\Enums\Papel;
use App\Enums\ServicoCategoria;
use App\Enums\TipoMovimento;
use App\Livewire\Conta\Perfil;
use App\Models\BibliotecaServico;
use App\Models\CategoriaPessoal;
use App\Models\Cliente;
use App\Models\Conta;
use App\Models\MovimentoPessoal;
use App\Models\Retirada;
use App\Models\User;
use App\Services\DadosDaConta;
use App\Services\PrepararConta;
use App\Services\Retiradas;
use App\Support\ContaActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

/** Export your data and delete your account, as the privacy policy promises. */
class DadosDaContaTest extends TestCase
{
    use RefreshDatabase;

    private function ficheirosDoZip(string $caminho): array
    {
        $zip = new ZipArchive;
        $zip->open($caminho);
        $ficheiros = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nome = $zip->getNameIndex($i);
            $ficheiros[$nome] = $zip->getFromName($nome);
        }
        $zip->close();

        return $ficheiros;
    }

    public function test_the_business_export_has_only_this_contas_data(): void
    {
        Cliente::create(['nome' => 'Cliente Meu', 'email' => 'a@t.mz', 'status' => 'activo', 'desde' => '2026-01-01']);
        $outra = $this->outraConta();
        ContaActual::usar($outra, fn () => Cliente::create(['nome' => 'Cliente Alheio', 'email' => 'b@t.mz', 'status' => 'activo', 'desde' => '2026-01-01']));

        $ficheiros = $this->ficheirosDoZip(app(DadosDaConta::class)->zipEmpresa($this->conta));

        $this->assertArrayHasKey('clientes.csv', $ficheiros);
        $this->assertArrayHasKey('pagamentos.csv', $ficheiros);
        $this->assertStringContainsString('Cliente Meu', $ficheiros['clientes.csv']);
        $this->assertStringNotContainsString('Cliente Alheio', $ficheiros['clientes.csv']);
        $this->assertStringNotContainsString('conta_id', $ficheiros['clientes.csv']);
    }

    public function test_the_downloads_come_out_of_the_profile_page(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Perfil::class)->call('exportarPessoal')->assertFileDownloaded('vencia-o-meu-pessoal.zip');
        Livewire::test(Perfil::class)->call('exportarEmpresa', $this->conta->id)->assertFileDownloaded('vencia-conta-de-teste.zip');
    }

    public function test_only_the_owner_can_export_a_business(): void
    {
        $this->actingAs(User::factory()->papel(Papel::Gestor)->create());

        Livewire::test(Perfil::class)->call('exportarEmpresa', $this->conta->id)->assertForbidden();
    }

    public function test_the_personal_export_has_only_my_entries(): void
    {
        $eu = User::factory()->create();
        $this->actingAs($eu);
        $categoria = CategoriaPessoal::create(['nome' => 'Casa', 'tipo' => TipoMovimento::Saida, 'icone' => 'house', 'cor' => 'azul', 'ordem' => 1]);
        MovimentoPessoal::create(['tipo' => TipoMovimento::Saida, 'descricao' => 'Renda de casa', 'categoria_pessoal_id' => $categoria->id, 'valor' => 9000, 'data' => '2026-10-01']);

        $outro = User::factory()->create();
        $this->actingAs($outro);
        $cat2 = CategoriaPessoal::create(['nome' => 'Lazer', 'tipo' => TipoMovimento::Saida, 'icone' => 'gift', 'cor' => 'verde', 'ordem' => 1]);
        MovimentoPessoal::create(['tipo' => TipoMovimento::Saida, 'descricao' => 'Segredo do outro', 'categoria_pessoal_id' => $cat2->id, 'valor' => 1, 'data' => '2026-10-01']);

        $this->actingAs($eu);
        $ficheiros = $this->ficheirosDoZip(app(DadosDaConta::class)->zipPessoal());

        $this->assertStringContainsString('Renda de casa', $ficheiros['movimentos.csv']);
        $this->assertStringNotContainsString('Segredo do outro', $ficheiros['movimentos.csv']);
    }

    public function test_deleting_a_solo_account_removes_the_user_and_their_business(): void
    {
        ContaActual::definir(null);
        $user = User::factory()->create(['password' => 'segredo123']);
        $conta = app(PrepararConta::class)->criar($user, 'Só minha');
        ContaActual::usar($conta, fn () => Cliente::create(['nome' => 'X', 'email' => 'x@t.mz', 'status' => 'activo', 'desde' => '2026-01-01']));

        $this->actingAs($user);
        Livewire::test(Perfil::class)->set('confirmarApagar', 'APAGAR')->set('passwordApagar', 'segredo123')->call('apagarConta')
            ->assertRedirect(route('login'));

        $this->assertNull(User::find($user->id));
        $this->assertNull(Conta::find($conta->id));
        $this->assertSame(0, Cliente::withoutGlobalScope('conta')->where('conta_id', $conta->id)->count());
    }

    public function test_the_wrong_password_deletes_nothing(): void
    {
        $user = User::factory()->create(['password' => 'segredo123']);
        $this->actingAs($user);

        Livewire::test(Perfil::class)->set('confirmarApagar', 'APAGAR')->set('passwordApagar', 'errada')->call('apagarConta')
            ->assertHasErrors('confirmarApagar');

        $this->assertNotNull(User::find($user->id));
    }

    public function test_the_only_owner_of_a_team_must_hand_it_over_first(): void
    {
        $dono = User::factory()->create(['password' => 'segredo123']);
        User::factory()->papel(Papel::Registos)->create();

        $this->assertNotEmpty(app(DadosDaConta::class)->planoParaApagar($dono)['bloqueios']);

        $this->actingAs($dono);
        Livewire::test(Perfil::class)->set('confirmarApagar', 'APAGAR')->set('passwordApagar', 'segredo123')->call('apagarConta')
            ->assertHasErrors('confirmarApagar');
        $this->assertNotNull(User::find($dono->id));
    }

    public function test_a_co_owner_leaving_keeps_the_business_and_hands_over_withdrawals(): void
    {
        $fica = User::factory()->create();
        $sai = User::factory()->create(['password' => 'segredo123']);

        $this->actingAs($sai);
        app(Retiradas::class)->retirar(300, Carbon::today(), null, null);

        app(DadosDaConta::class)->apagarUtilizador($sai);

        $this->assertNotNull(Conta::find($this->conta->id));
        $this->assertSame($fica->id, Retirada::query()->sole()->user_id);
    }

    public function test_the_last_super_admin_cannot_delete_themself(): void
    {
        $admin = User::factory()->create(['super_admin' => true]);

        $this->assertNotEmpty(app(DadosDaConta::class)->planoParaApagar($admin)['bloqueios']);
    }

    public function test_everyday_categories_exist_and_new_contas_use_them(): void
    {
        $this->assertSame('Educação e explicações', ServicoCategoria::Educacao->label());
        $this->assertSame(ServicoCategoria::Educacao, ServicoCategoria::cases()[0], 'everyday categories come first');

        ContaActual::definir(null);
        $conta = app(PrepararConta::class)->criar(User::factory()->create(), 'Ginásio Força');
        $categorias = ContaActual::usar($conta, fn () => BibliotecaServico::query()->pluck('categoria')->map->value->unique()->all());

        $this->assertContains('saude', $categorias);
        $this->assertContains('imoveis', $categorias);
    }
}
