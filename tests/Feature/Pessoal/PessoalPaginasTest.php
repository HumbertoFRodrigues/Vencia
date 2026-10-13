<?php

namespace Tests\Feature\Pessoal;

use App\Livewire\Pessoal\MovimentoDialog;
use App\Models\CategoriaPessoal;
use App\Models\FixoPessoal;
use App\Models\MovimentoPessoal;
use App\Models\User;
use App\Services\FinancasPessoais;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class PessoalPaginasTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/pessoal')->assertRedirect('/login');
    }

    public function test_every_personal_page_renders(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/pessoal', '/pessoal/movimentos', '/pessoal/fixos', '/pessoal/orcamentos', '/pessoal/analise', '/pessoal/movimentos?tipo=xpto&mes=2026-99', '/pessoal/analise?ano=99999'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_a_movement_is_created_through_the_dialog(): void
    {
        $this->actingAs(User::factory()->create());
        app(FinancasPessoais::class)->garantirCategoriasPadrao();

        Livewire::test(MovimentoDialog::class)
            ->call('abrir', 'saida')
            ->call('guardar')
            ->assertHasErrors(['descricao', 'valor']);

        foreach (['1.234,50' => '1234.50', '1.500' => '1500.00', '99.5' => '99.50'] as $digitado => $guardado) {
            Livewire::test(MovimentoDialog::class)
                ->call('abrir', 'saida')
                ->set('descricao', 'Farmácia')
                ->set('valor', $digitado)
                ->call('guardar')
                ->assertHasNoErrors();

            $this->assertSame($guardado, MovimentoPessoal::latest('id')->first()->valor, "typed {$digitado}");
        }
    }

    public function test_locked_ids_cannot_be_tampered_with(): void
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(MovimentoDialog::class)->set('fixoId', 1);
    }

    public function test_another_users_fixo_cannot_be_opened(): void
    {
        $dono = User::factory()->create();
        $this->actingAs($dono);
        app(FinancasPessoais::class)->garantirCategoriasPadrao();
        $fixo = FixoPessoal::create([
            'tipo' => 'saida', 'descricao' => 'Renda', 'categoria_pessoal_id' => CategoriaPessoal::first()->id,
            'valor' => 100, 'dia_mes' => 1, 'inicio' => now()->startOfMonth(),
        ]);

        $this->actingAs(User::factory()->create());

        $this->expectException(ModelNotFoundException::class);
        Livewire::test(MovimentoDialog::class)->call('abrir', null, null, $fixo->id, now()->format('Y-m'));
    }
}
