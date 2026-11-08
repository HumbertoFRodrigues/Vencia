<?php

/*
 * Stand-ins for PHP extensions that shared hosting (cPanel) often ships off,
 * so Vencia keeps working until they are switched on in Select PHP Version →
 * Extensions. Each one loads only when the real extension is missing.
 * Admin → Visão geral → Estado do sistema lists what is missing.
 */

require __DIR__.'/fileinfo.php';

if (! extension_loaded('mbstring') && ! function_exists('mb_strimwidth')) {
    require __DIR__.'/mbstring-substituto.php';
}
