<?php

namespace Innoboxrr\Traits\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MetaCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meta:cleanup {models* : Lista de modelos Meta separados por espacios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia duplicados y agrega índice único a las tablas Meta especificadas';

    public function handle()
    {
        $models = $this->argument('models');

        foreach ($models as $model) {
            $tableName = Str::plural(Str::snake($model));
            $baseColumn = Str::snake(Str::beforeLast($model, 'Meta')) . '_id';
            $indexName = "unique_key_{$baseColumn}";

            $this->info("Procesando tabla: {$tableName}...");

            DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_unique_rows");

            DB::statement("
                CREATE TEMPORARY TABLE temp_unique_rows AS
                SELECT MIN(id) AS id
                FROM {$tableName}
                GROUP BY `key`, {$baseColumn}
            ");

            DB::statement("
                DELETE FROM {$tableName}
                WHERE id NOT IN (
                    SELECT id FROM temp_unique_rows
                )
            ");

            DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_unique_rows");

            if (!$this->indexExists($tableName, $indexName)) {
                Schema::table($tableName, function ($table) use ($baseColumn, $indexName) {
                    $table->unique(['key', $baseColumn], $indexName);
                });
                $this->info("\t✔ Índice agregado: {$indexName}");
            } else {
                $this->info("\t⚠ Índice ya existe: {$indexName}, se omite");
            }
        }

        $this->info('Limpieza completada correctamente.');
    }

    protected function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();
    
        $result = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    
        return $result;
    }
}
