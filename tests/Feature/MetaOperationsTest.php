<?php

namespace Innoboxrr\Traits\Tests\Feature;

use Illuminate\Http\Request;
use Innoboxrr\Traits\Tests\Support\Article;
use Innoboxrr\Traits\Tests\Support\ArticleMeta;
use Innoboxrr\Traits\Tests\TestCase;

/**
 * Es el trait del que depende cada modelo con `metas: true` que genera
 * larapack, y no lo cubría nada.
 */
final class MetaOperationsTest extends TestCase
{
    private function article(): Article
    {
        return Article::create(['title' => 'Hola']);
    }

    public function test_meta_devuelve_el_valor_guardado(): void
    {
        $article = $this->article();

        $article->setMeta('seo_title', 'Título SEO');

        $this->assertSame('Título SEO', $article->meta('seo_title'));
    }

    public function test_meta_devuelve_el_valor_por_defecto_si_no_existe(): void
    {
        $this->assertSame('nada', $this->article()->meta('inexistente', 'nada'));
        $this->assertNull($this->article()->meta('inexistente'));
    }

    public function test_set_meta_actualiza_en_vez_de_duplicar(): void
    {
        $article = $this->article();

        $article->setMeta('seo_title', 'Primero');
        $article->setMeta('seo_title', 'Segundo');

        $this->assertSame(1, ArticleMeta::where('key', 'seo_title')->count());
        $this->assertSame('Segundo', $article->meta('seo_title'));
    }

    public function test_set_metas_escribe_varias_de_una_vez(): void
    {
        $article = $this->article();

        $article->setMetas(['seo_title' => 'T', 'seo_description' => 'D']);

        $this->assertSame('T', $article->meta('seo_title'));
        $this->assertSame('D', $article->meta('seo_description'));
    }

    /**
     * La clave foránea se deriva de la relación. Si alguien renombra la
     * relación o el modelo meta, esto es lo que se rompe.
     */
    public function test_set_metas_deriva_la_clave_foranea_de_la_relacion(): void
    {
        $article = $this->article();

        $article->setMetas(['seo_title' => 'T']);

        $this->assertDatabaseHas('article_metas', [
            'article_id' => $article->id,
            'key' => 'seo_title',
        ]);
    }

    // WHITELIST

    /**
     * `editable_metas` es la lista blanca: lo que no está ahí no se guarda.
     * Es la garantía de que un campo de más en la petición no acaba en la
     * base de datos.
     */
    public function test_metas_array_solo_deja_pasar_las_metas_editables(): void
    {
        $article = $this->article();

        $this->assertSame(
            ['seo_title' => 'T'],
            $article->metas_array(['seo_title' => 'T', 'inventada' => 'X'])
        );
    }

    public function test_metas_array_acepta_un_request(): void
    {
        $article = $this->article();

        $request = Request::create('/', 'POST', ['seo_title' => 'T', 'inventada' => 'X']);

        $this->assertSame(['seo_title' => 'T'], $article->metas_array($request));
    }

    public function test_metas_array_ignora_lo_que_no_es_arreglo_ni_request(): void
    {
        $this->assertSame([], $this->article()->metas_array('cadena suelta'));
    }

    // UPDATE

    public function test_update_metas_crea_y_actualiza(): void
    {
        $article = $this->article();

        $article->update_metas(['seo_title' => 'Uno'], ArticleMeta::class, 'article_id');
        $article->update_metas(['seo_title' => 'Dos'], ArticleMeta::class, 'article_id');

        $this->assertSame(1, ArticleMeta::where('key', 'seo_title')->count());
        $this->assertSame('Dos', $article->fresh()->meta('seo_title'));
    }

    /**
     * Enviar un valor vacío es como se borra una meta desde el formulario.
     */
    public function test_update_metas_borra_la_meta_cuyo_valor_se_vacia(): void
    {
        $article = $this->article();

        $article->update_metas(['seo_title' => 'Algo'], ArticleMeta::class, 'article_id');
        $article->update_metas(['seo_title' => '   '], ArticleMeta::class, 'article_id');

        $this->assertSame(0, ArticleMeta::where('key', 'seo_title')->count());
    }

    public function test_update_metas_serializa_un_arreglo_como_json(): void
    {
        $article = $this->article();

        $article->update_metas(['seo_title' => ['a', 'b']], ArticleMeta::class, 'article_id');

        $this->assertSame('["a","b"]', $article->fresh()->meta('seo_title'));
    }

    public function test_update_metas_ignora_lo_que_no_esta_en_la_lista_blanca(): void
    {
        $article = $this->article();

        $article->update_metas(['inventada' => 'X'], ArticleMeta::class, 'article_id');

        $this->assertSame(0, ArticleMeta::where('key', 'inventada')->count());
    }

    // PAYLOAD

    public function test_get_payload_lee_con_notacion_de_puntos(): void
    {
        $article = Article::create(['title' => 'Hola', 'payload' => ['seo' => ['title' => 'T']]]);

        $this->assertSame('T', $article->getPayload('seo.title'));
        $this->assertSame('def', $article->getPayload('seo.falta', 'def'));
    }

    public function test_get_payload_sin_payload_devuelve_el_defecto(): void
    {
        $this->assertSame('def', $this->article()->getPayload('lo.que.sea', 'def'));
    }
}
