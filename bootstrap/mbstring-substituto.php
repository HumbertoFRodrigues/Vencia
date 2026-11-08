<?php

// Loaded by bootstrap/extensoes.php only when PHP has no mbstring extension.
// symfony/polyfill-mbstring covers the rest of mb_*, but not mb_strimwidth,
// which Laravel's Str::limit and Vencia's activity log use.

function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
{
    $texto = mb_substr($string, $start, null, $encoding);
    if (mb_strwidth($texto, $encoding) <= $width) {
        return $texto;
    }

    $largura = $width - mb_strwidth($trim_marker, $encoding);
    $saida = '';
    $usada = 0;
    foreach (preg_split('//u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $letra) {
        $w = mb_strwidth($letra, $encoding);
        if ($usada + $w > $largura) {
            break;
        }
        $saida .= $letra;
        $usada += $w;
    }

    return $saida.$trim_marker;
}
