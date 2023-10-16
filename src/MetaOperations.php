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

    public function payload(string $key, $default = null)
    {
        if(isset($this->payload) && is_array($this->payload)) {

            return Arr::get($this->payload, $key, $default);

        } 

        return $default;
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
    	$event_class = (!is_null($event_class)) ? app($event_class) : null ;
	
        // Recorrer cada elemento del arreglo
        foreach ($metas as $key => $meta) {

            // Convert meta to assignable value
            $meta = $this->parse_meta($meta); 
            
            // Si no es nulo
            if($this->validate_meta($meta)){
            
                // Actualizar el valor
                $new_meta = $model_meta_class::updateOrCreate([
                    'key' => $key,
                    $related => $this->id
                ],[
                    'value' => $meta
                ]);

                if(!is_null($event_class)) event(new $event_class($new_meta));

            // Si si es nulo.
            }else{
            
                // Buscar si el valor meta existe                
                $meta = $model_meta_class::where('key', $key)->where($related, $this->id)->first();
            
                // Si existe y no se ha pasado valor, se debe eliminar
                if(!is_null($meta)) $meta->delete();
            
            }

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