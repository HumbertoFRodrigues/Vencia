<?php

namespace Tests\Feature\Contas;

use App\Livewire\Admin\Plataforma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** AdSense on the public pages, switched on in Admin → Plataforma. */
class AnunciosTest extends TestCase
{
    use RefreshDatabase;

    public function test_off_by_default_no_script_and_no_ads_txt(): void
    {
        $this->get('/')->assertOk()->assertDontSee('adsbygoogle', false);
        $this->get('/ads.txt')->assertNotFound();
    }

    public function test_pasting_googles_script_keeps_the_id_and_shows_the_ads(): void
    {
        $this->actingAs(User::factory()->create(['super_admin' => true]));

        Livewire::test(Plataforma::class)
            ->set('anunciosCliente', '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456" crossorigin="anonymous"></script>')
            ->set('anunciosLigado', true)
            ->call('guardarAnuncios')->assertHasNoErrors()
            ->assertSet('anunciosCliente', 'ca-pub-1234567890123456');

        auth()->logout();

        $this->get('/')->assertOk()->assertSee('adsbygoogle.js?client=ca-pub-1234567890123456', false);
        $this->get('/termos')->assertOk()->assertSee('ca-pub-1234567890123456', false);
        $this->get('/login')->assertOk()->assertDontSee('adsbygoogle', false);
        $this->get('/ads.txt')->assertOk()->assertSeeText('google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0');
    }

    public function test_a_bad_id_is_refused(): void
    {
        $this->actingAs(User::factory()->create(['super_admin' => true]));

        Livewire::test(Plataforma::class)
            ->set('anunciosCliente', '"><script>alert(1)</script>')->set('anunciosLigado', true)
            ->call('guardarAnuncios')->assertHasErrors(['anunciosCliente']);

        $this->get('/ads.txt')->assertNotFound();
    }
}
