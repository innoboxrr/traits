<?php

namespace Innoboxrr\Traits\Tests\Feature;

use Innoboxrr\Traits\Tests\Support\Article;
use Innoboxrr\Traits\Tests\TestCase;

final class DumpsGlobalScopesTest extends TestCase
{
    protected function tearDown(): void
    {
        Article::clearBootedModels();

        parent::tearDown();
    }

    public function test_un_modelo_sin_scopes_devuelve_una_lista_vacia(): void
    {
        $this->assertSame([], Article::dumpMyGlobalScopes());
    }

    public function test_nombra_la_clase_de_un_scope_de_objeto(): void
    {
        Article::addGlobalScope(new PublishedScope());

        $this->assertContains(PublishedScope::class, Article::dumpMyGlobalScopes());
    }

    /**
     * El renombrado de dumpGlobalScopes a dumpMyGlobalScopes fue para
     * distinguir los closures del resto de callables: un closure no tiene
     * clase que nombrar.
     */
    public function test_un_scope_de_closure_se_nombra_closure(): void
    {
        Article::addGlobalScope('reciente', function ($query) {
            return $query;
        });

        $this->assertContains('closure', Article::dumpMyGlobalScopes());
    }
}
