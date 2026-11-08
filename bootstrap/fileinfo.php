<?php

/*
 * Stand-in for PHP's "fileinfo" extension, loaded only when the server lacks
 * it (cPanel often ships with it off). Without it every upload fails with
 * "Unable to guess the MIME type": Laravel's validation and Livewire's
 * temporary uploads both call finfo. Detection is by the file's first bytes
 * (App\Support\TipoDeFicheiro). Turning the real extension on is still
 * better, and then this file does nothing.
 */

if (! extension_loaded('fileinfo') && ! class_exists('finfo', false)) {
    require __DIR__.'/fileinfo-substituto.php';
}
