<?php

namespace Innoboxrr\Traits\Tests\Unit;

use Innoboxrr\Traits\Tests\Support\Toolbox;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SemVerOperationsTest extends TestCase
{
    private Toolbox $toolbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->toolbox = new Toolbox();
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function increments(): array
    {
        return [
            'parche' => ['1.2.3', 'patch', '1.2.4'],
            'menor reinicia el parche' => ['1.2.3', 'minor', '1.3.0'],
            'mayor reinicia menor y parche' => ['1.2.3', 'major', '2.0.0'],
            'desde cero' => ['0.0.0', 'patch', '0.0.1'],
        ];
    }

    #[DataProvider('increments')]
    public function test_incrementa_la_version(string $from, string $type, string $expected): void
    {
        $this->assertSame($expected, $this->toolbox->incrementVersion($from, $type));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function decrements(): array
    {
        return [
            'parche' => ['1.2.3', 'patch', '1.2.2'],
            'menor' => ['1.2.3', 'minor', '1.1.0'],
            'mayor' => ['2.2.3', 'major', '1.0.0'],
        ];
    }

    #[DataProvider('decrements')]
    public function test_decrementa_la_version(string $from, string $type, string $expected): void
    {
        $this->assertSame($expected, $this->toolbox->decrementVersion($from, $type));
    }

    public function test_compara_versiones(): void
    {
        $this->assertSame(-1, $this->toolbox->compareVersions('1.0.0', '1.0.1'));
        $this->assertSame(1, $this->toolbox->compareVersions('1.1.0', '1.0.9'));
        $this->assertSame(0, $this->toolbox->compareVersions('1.2.3', '1.2.3'));
    }

    /**
     * La comparación tiene que ser numérica y no de cadenas: '10' > '9'.
     */
    public function test_compara_por_numero_y_no_por_cadena(): void
    {
        $this->assertSame(1, $this->toolbox->compareVersions('1.10.0', '1.9.0'));
    }

    public function test_valida_el_formato(): void
    {
        $this->assertTrue($this->toolbox->isValidVersion('1.2.3'));
        $this->assertFalse($this->toolbox->isValidVersion('1.2'));
        $this->assertFalse($this->toolbox->isValidVersion('v1.2.3'));
        $this->assertFalse($this->toolbox->isValidVersion('1.2.3-beta'));
    }
}
