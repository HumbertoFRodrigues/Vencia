<?php

namespace Tests\Unit;

use App\Http\PedidoComBase;
use PHPUnit\Framework\TestCase;

/** The app reached as localhost/vencia through the project root's .htaccess (see public/index.php). */
class PedidoComBaseTest extends TestCase
{
    private function pedido(string $uri): PedidoComBase
    {
        return PedidoComBase::create($uri, 'GET', [], [], [], [
            'VENCIA_BASE_URL' => '/vencia',
            'SCRIPT_NAME' => '/Vencia/public/index.php',
            'SCRIPT_FILENAME' => 'C:/xampp/htdocs/Vencia/public/index.php',
        ]);
    }

    public function test_pages_in_the_subfolder_resolve_to_app_paths(): void
    {
        $this->assertSame('/vencia', $this->pedido('http://localhost/vencia/clientes')->getBaseUrl());
        $this->assertSame('/clientes', $this->pedido('http://localhost/vencia/clientes')->getPathInfo());
        $this->assertSame('/', $this->pedido('http://localhost/vencia/')->getPathInfo());
    }

    public function test_home_without_trailing_slash_still_resolves(): void
    {
        // What route:cache's matcher does to "/vencia/" before matching.
        $pedido = $this->pedido('http://localhost/vencia/')->duplicate();
        $pedido->server->set('REQUEST_URI', '/vencia');

        $this->assertSame('/', $pedido->getPathInfo());
        $this->assertSame('http://localhost/vencia', $pedido->getSchemeAndHttpHost().$pedido->getBaseUrl());
    }

    public function test_without_a_fixed_base_it_behaves_like_laravel(): void
    {
        $pedido = PedidoComBase::create('http://exemplo.co.mz/clientes', 'GET', [], [], [], ['SCRIPT_NAME' => '/index.php', 'SCRIPT_FILENAME' => '/home/u/vencia/public/index.php']);

        $this->assertSame('', $pedido->getBaseUrl());
        $this->assertSame('/clientes', $pedido->getPathInfo());
    }
}
