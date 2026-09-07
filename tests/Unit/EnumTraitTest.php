<?php

namespace Innoboxrr\Traits\Tests\Unit;

use Innoboxrr\Traits\Tests\Support\Status;
use PHPUnit\Framework\TestCase;

final class EnumTraitTest extends TestCase
{
    public function test_lista_valores_y_nombres(): void
    {
        $this->assertSame(['draft', 'published', 'archived'], Status::getValues());
        $this->assertSame(['Draft', 'Published', 'Archived'], Status::getKeys());
    }

    /**
     * getKey busca por valor sobre getValues(), que devuelve una lista: lo que
     * sale es la posicion, no el nombre del caso.
     */
    public function test_get_key_devuelve_la_posicion_del_valor(): void
    {
        $this->assertSame('1', (string) Status::getKey('published'));
        $this->assertNull(Status::getKey('inexistente'));
    }

    public function test_get_value_traduce_el_valor_del_caso(): void
    {
        $this->assertSame('draft', Status::getValue('draft'));
        $this->assertNull(Status::getValue('inexistente'));
    }

    public function test_valida_valores(): void
    {
        $this->assertTrue(Status::isValid('draft'));
        $this->assertTrue(Status::isValidValue('archived'));
        $this->assertFalse(Status::isValid('borrador'));
    }

    public function test_valida_nombres(): void
    {
        $this->assertTrue(Status::isValidKey('Draft'));
        $this->assertFalse(Status::isValidKey('draft'));
    }
}
