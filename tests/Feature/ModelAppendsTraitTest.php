<?php

namespace Innoboxrr\Traits\Tests\Feature;

use Innoboxrr\Traits\Tests\Support\Article;
use Innoboxrr\Traits\Tests\TestCase;

final class ModelAppendsTraitTest extends TestCase
{
    public function test_anade_un_accessor_a_la_serializacion(): void
    {
        $article = Article::create(['title' => 'hola']);

        $this->assertArrayNotHasKey('shouted_title', $article->toArray());

        $article->setAppends(['shouted_title']);

        $this->assertSame('HOLA', $article->toArray()['shouted_title']);
    }

    /**
     * Es lo que hace el Resource generado: llamarlo una vez por relacion
     * pedida. Sin la deduplicacion, el mismo accessor se serializaria varias
     * veces.
     */
    public function test_no_duplica_lo_que_ya_estaba(): void
    {
        $article = Article::create(['title' => 'hola']);

        $article->setAppends(['shouted_title']);
        $article->setAppends(['shouted_title']);

        $this->assertSame(['shouted_title'], array_values($article->getAppends()));
    }
}
