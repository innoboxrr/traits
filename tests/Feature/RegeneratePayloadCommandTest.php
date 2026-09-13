<?php

namespace Innoboxrr\Traits\Tests\Feature;

use Innoboxrr\Traits\Tests\Support\Article;
use Innoboxrr\Traits\Tests\TestCase;

final class RegeneratePayloadCommandTest extends TestCase
{
    /**
     * El comando registraba el fallo con `Log::error` sin importar la fachada:
     * el primer modelo que fallaba tumbaba el comando con "Class Log not found"
     * en lugar de avisar y seguir.
     */
    public function test_un_modelo_que_falla_se_avisa_sin_tumbar_el_comando(): void
    {
        // Article no define updatePayload(): llamarlo lanza una excepción.
        $article = Article::create(['title' => 'Hola']);

        $this->artisan('metas:regpayload', ['modelClass' => Article::class, '--modelId' => $article->id])
            ->expectsOutputToContain('Error al actualizar el payload')
            ->assertExitCode(0);
    }

    public function test_una_clase_que_no_existe_se_explica(): void
    {
        $this->artisan('metas:regpayload', ['modelClass' => 'No\\Existe'])
            ->expectsOutputToContain('no existe')
            ->assertExitCode(0);
    }
}
