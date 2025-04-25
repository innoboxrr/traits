<?php

/**
 * Este trait se aplica para modelos que emplean la configuración [Model]Meta
 * 
 * Permite recuperar, crear y actualizar metainformación del modelo
 * 
 * Es necesario que el modelo que la emplee haga uso de la propiedad "editable_metas" y la relación metas()
 */

namespace Innoboxrr\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;


trait MetaOperations
{	
    
    /**
     * Retorna metainformación
     * @param  string $key     Clave del valor
     * @param  string $default Valor a retornar si no se encuentrA
     * @return string          
     */
	public function meta($key, $default = null)
    {
        $meta = $this->metas()->where('key', $key)->first();
        if(!is_null($meta)){
            return $meta->value;
        }else{
            return $default;
        }
    }

    public function getPayload(string $key, $default = null)
    {
        if(isset($this->payload) && is_array($this->payload)) {
            return Arr::get($this->payload, $key, $default);
        } 
        return $default;
    }

    public function setMeta($key, $value)
    {
        $this->metas()->updateOrCreate([
            'key' => $key
        ],[
            'value' => $value
        ]);
        return $this;
    }

    public function setMetas(array $metas, string $foreignKey = null)
    {
        $data = [];

        // Determinar dinámicamente la clave foránea
        $foreignKey = $foreignKey ?? $this->metas()->getForeignKeyName();

        if (!$foreignKey) {
            throw new \Exception("No se pudo determinar la clave foránea para la relación 'metas()'.");
        }

        foreach ($metas as $key => $value) {
            $data[] = [
                $foreignKey => $this->getKey(), // Obtiene el ID del modelo actual
                'key' => $key,
                'value' => $value,
            ];
        }

        // Validar que realmente se obtuvo la clave foránea
        if (empty($data) || !isset($data[0][$foreignKey])) {
            throw new \Exception("La clave foránea '{$foreignKey}' no se está asignando correctamente.");
        }

        // Actualiza o crea todas las etiquetas de forma masiva
        $this->metas()->upsert(
            $data,
            [$foreignKey, 'key'], // Claves para determinar si debe actualizar
            ['value'] // Columnas a actualizar
        );

        return $this;
    }

	/*
	 * $metas: Solicitud de actualización del usuario, Puede ser un objeto Request o un arreglo asociativo
	 * $model_meta_class: Metamodelo que se va a actualizar Ej. ProductMeta
	 * $related: columna relacionada del modelo que se va a actualizar Ej. product_id
	 * $event_class: Disparador de clase que se lanzaría tras la actualización de un modelo
	 */
    public function update_metas($metas, $model_meta_class, $related, $event_class = null)
    {
        // Crear el arreglo de metas
        $metas = $this->metas_array($metas);
        
        // Definir el MetaModelo que se va a modificar
        $model_meta_class = app($model_meta_class);

        // Definir el evento a disparar en la actualización de clase
        $event_class = (!is_null($event_class)) ? app($event_class) : null;

        // Validar y procesar las metas antes de interactuar con la base de datos
        $valid_metas = [];
        $metas_to_delete = [];
        
        foreach ($metas as $key => $meta) {
            // Convertir meta a un valor asignable
            $meta = $this->parse_meta($meta);

            if ($this->validate_meta($meta)) {
                $valid_metas[] = [
                    'key' => $key,
                    $related => $this->id,
                    'value' => $meta,
                ];
            } else {
                $metas_to_delete[] = $key;
            }
        }

        // Realizar un upsert masivo para las metas válidas
        if (!empty($valid_metas)) {
            $model_meta_class::upsert(
                $valid_metas,
                ['key', $related], // Claves únicas para determinar duplicados
                ['value'] // Columnas a actualizar
            );

            // Disparar eventos para cada meta actualizada
            if (!is_null($event_class)) {
                foreach ($valid_metas as $meta_data) {
                    $new_meta = $model_meta_class::where('key', $meta_data['key'])
                                                ->where($related, $this->id)
                                                ->first();
                    event(new $event_class($new_meta));
                }
            }
        }

        // Eliminar metas inválidas
        if (!empty($metas_to_delete)) {
            $model_meta_class::whereIn('key', $metas_to_delete)
                ->where($related, $this->id)
                ->delete();
        }

        return $this;
    }

    public function metas_array($metas)
    {
    	// Verificar que en el modelo principal se ha definido el atributo editable_metas
    	if(isset($this->editable_metas)){
            // Arreglo de las métas que se deberán actualizar
            $metas_array = [];
            // Metas que están permitidas en el sistem 
            $editable_metas = $this->editable_metas;
            // Analizar la variable metas
            $metas_values = $this->parse_metas($metas); 
            // Analizar cada variable del arreglo
            foreach ($metas_values as $key => $value) {
                if(in_array($key, $editable_metas)){
                    $metas_array += [$key => $value];
                }
            }
            // Retornar arreglo
            return $metas_array;
    	}
    }

    protected function parse_metas($metas)
    {
        if($metas instanceof Request){
            return $metas->all();
        }elseif (is_array($metas)){
            return $metas;
        }else{
            return [];
        }
    }

    protected function parse_meta($meta) 
    {
        if(is_array($meta)) {
            return json_encode($meta);
        }
        return $meta;
    }

    protected function validate_meta($meta) 
    {
        if(is_array($meta)) {
            return count($meta) > 0;
        }
        if(is_string($meta)) {
            $meta = trim($meta);
            return ($meta != '' && $meta != null);
        }
        if(is_null($meta)) {
            return false;
        }
        return true;
    }
}