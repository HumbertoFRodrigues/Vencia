<?php

/*
 * Compiled Blade views go in a folder per release (the VERSAO file shipped
 * with each update ZIP). Without terminal access a new release could not
 * clear the old cache, and Blade's "is the source newer?" check fails when
 * a ZIP keeps older file dates: new PHP code ended up rendering old pages.
 */
$versao = preg_replace('/[^A-Za-z0-9._-]/', '', trim((string) @file_get_contents(dirname(__DIR__).'/VERSAO'))) ?: 'dev';

return [

    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views/'.$versao)),

];
