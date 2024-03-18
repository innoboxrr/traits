<?php

namespace Innoboxrr\Traits\Console\Commands;

use Illuminate\Console\Command;

class RegeneratePayloadCommand extends Command
{
    
    protected $signature = 'metas:regpayload {modelClass} {--modelId=}';

    protected $description = 'Regenerate payload structure for model or models';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $modelClass = $this->argument('modelClass');
        $modelId = $this->option('modelId');

        if (!class_exists($modelClass)) {
            $this->error("La clase {$modelClass} no existe.");
            return;
        }

        $instance = new $modelClass;

        if (!$instance instanceof \Illuminate\Database\Eloquent\Model) {
            $this->error("La clase {$modelClass} no es una instancia de Eloquent Model.");
            return;
        }

        if ($modelId) {
            $model = $modelClass::find($modelId);
            if (!$model) {
                $this->error("No se encontró el modelo con ID {$modelId}.");
                return;
            }
            $this->updatePayload($model);
        } else {
            $modelClass::chunk(100, function ($models) {
                foreach ($models as $model) {
                    $this->updatePayload($model);
                }
            });
        }

        $this->info("Payload actualizado correctamente.");
    }

    protected function updatePayload($model)
    {
        try {
            $model->updatePayload();
        } catch (\Exception $e) {
            Log::error("Error al actualizar el payload: {$e->getMessage()}");
            $this->error("Error al actualizar el payload para el modelo con ID {$model->id}.");
        }
    }

}
