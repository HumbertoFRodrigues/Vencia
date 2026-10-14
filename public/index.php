<?php

use App\Http\PedidoComBase;
use Illuminate\Foundation\Application;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Reached through the project root's .htaccess (document root = project
// folder, e.g. localhost/vencia or a cPanel domain pointing at the app
// folder): the browser URL has no "/public", so tell Laravel where the app
// lives (see PedidoComBase). Otherwise every page in a subfolder answers 404.
$script = $_SERVER['SCRIPT_NAME'] ?? '';
if (str_ends_with($script, '/public/index.php')) {
    $base = substr($script, 0, -strlen('/public/index.php'));
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Keep the folder's case as typed in the address (Windows: /vencia vs folder "Vencia").
    if (strncasecmp($uri, $base, strlen($base)) === 0) {
        $base = substr($uri, 0, strlen($base));
    }
    $viaPublic = strncasecmp($uri, $base.'/public/', strlen($base) + 8) === 0 || strcasecmp(strtok($uri, '?'), $base.'/public') === 0;
    $_SERVER['VENCIA_BASE_URL'] = $viaPublic ? $base.'/public' : $base;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(PedidoComBase::capture());
