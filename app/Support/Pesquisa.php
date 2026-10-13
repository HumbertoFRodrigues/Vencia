<?php

namespace App\Support;

class Pesquisa
{
    /** "contains" LIKE pattern with the user's % and _ taken literally. */
    public static function contem(string $texto): string
    {
        return '%'.addcslashes(trim($texto), '\\%_').'%';
    }
}
