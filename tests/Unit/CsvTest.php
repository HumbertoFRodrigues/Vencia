<?php

namespace Tests\Unit;

use App\Support\Csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    public function test_formula_like_text_is_neutralised(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", Csv::celula('=HYPERLINK("x")'));
        $this->assertSame("'+SUM(A1)", Csv::celula('+SUM(A1)'));
        $this->assertSame("'-2+3", Csv::celula('-2+3'));
        $this->assertSame("'@SUM(A1)", Csv::celula('@SUM(A1)'));
    }

    public function test_normal_values_are_untouched(): void
    {
        $this->assertSame('Maria Costa', Csv::celula('Maria Costa'));
        $this->assertSame('-1500', Csv::celula('-1500'));
        $this->assertSame('1.500 MZN', Csv::celula('1.500 MZN'));
        $this->assertSame('', Csv::celula(null));
    }
}
