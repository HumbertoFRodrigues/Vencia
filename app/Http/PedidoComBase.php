<?php

namespace App\Http;

use Illuminate\Http\Request;

/**
 * The incoming request, with the app's base folder fixed by public/index.php
 * when the site is reached through the project root's .htaccess (XAMPP's
 * localhost/vencia, or a cPanel domain pointed at the app folder instead of
 * public/). Guessing it from SCRIPT_NAME fails there, most visibly with
 * `route:cache`, whose matcher retries the home page without its trailing
 * slash ("/vencia/" → "/vencia") and then answers 405.
 */
class PedidoComBase extends Request
{
    protected function prepareBaseUrl(): string
    {
        $base = $this->server->get('VENCIA_BASE_URL');

        return is_string($base) ? $base : parent::prepareBaseUrl();
    }
}
