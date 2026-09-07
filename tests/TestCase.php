<?php

namespace Innoboxrr\Traits\Tests;

use Illuminate\Database\Schema\Blueprint;
use Innoboxrr\Traits\Providers\AppServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [AppServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Las tablas que necesitan los modelos de prueba. Reproducen la forma que
     * `MetaOperations` da por supuesta: un modelo y su tabla `<modelo>_metas`
     * con `key` y `value`, que es lo que genera larapack.
     */
    protected function defineDatabaseMigrations(): void
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        $schema->create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        $schema->create('article_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['article_id', 'key']);
        });
    }
}
