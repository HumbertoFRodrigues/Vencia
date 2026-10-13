<?php

namespace App\Support;

/** Money typed the pt-PT/pt-MZ way ("1.234,50", "1 500", "1.500") or the dot-decimal way ("1234.50"). */
class Valor
{
    public static function normalizar(string $texto): string
    {
        $t = str_replace([' ', "\u{00A0}"], '', trim($texto));

        if (str_contains($t, ',')) {
            return str_replace(['.', ','], ['', '.'], $t);
        }

        // "1.500" / "12.345.678" are thousands separators, not decimals.
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $t)) {
            return str_replace('.', '', $t);
        }

        return $t;
    }
}
