<?php

namespace Innoboxrr\Traits\Tests\Unit;

use Innoboxrr\Traits\Tests\Support\Toolbox;
use PHPUnit\Framework\TestCase;

final class ArrayOperationsTest extends TestCase
{
    private Toolbox $toolbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->toolbox = new Toolbox();
    }

    public function test_is_not_empty_mira_los_valores_y_no_las_claves(): void
    {
        $this->assertTrue($this->toolbox->isNotEmpty(['a' => null, 'b' => 'algo']));
        $this->assertFalse($this->toolbox->isNotEmpty(['a' => null, 'b' => null]));
        $this->assertFalse($this->toolbox->isNotEmpty([]));
    }

    public function test_wrap_implode_envuelve_cada_elemento(): void
    {
        $this->assertSame(
            '<li>a</li><li>b</li>',
            $this->toolbox->wrapImplode(['a', 'b'], '<li>', '</li>')
        );
    }

    public function test_wrap_implode_respeta_el_separador(): void
    {
        $this->assertSame(
            "'a', 'b'",
            $this->toolbox->wrapImplode(['a', 'b'], "'", "'", ', ')
        );
    }

    public function test_wrap_implode_de_un_arreglo_vacio_es_cadena_vacia(): void
    {
        $this->assertSame('', $this->toolbox->wrapImplode([], '<li>', '</li>'));
    }
}
