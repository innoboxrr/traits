<?php

/**
 * Las metas de un modelo: pares clave/valor en su tabla <modelo>_metas, y una
 * copia en la columna JSON `payload` para leerlas sin consultas.
 *
 * El modelo que lo usa necesita:
 *
 * - la relación `metas()` hacia su modelo Meta (hasMany);
 * - `$editable_metas`: las claves que se pueden escribir desde un formulario;
 * - `$protected_metas`, opcional: claves que solo escribe el sistema. El camino
 *   del formulario (update_metas) las ignora aunque vengan en la petición y
 *   aunque figuren en `editable_metas`; setMeta() y setMetas() sí las escriben;
 * - una columna `payload` con cast array y un `updatePayload()`, si se quiere
 *   la copia en JSON. getPayload() la lee con notación de puntos.
 *
 * Los arreglos se guardan como JSON. En update_metas, un valor vacío (null,
 * cadena en blanco o arreglo vacío) borra la meta, y una clave que no llega no
 * se toca.
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

    /**
     * Escribe una meta desde el código. No pasa por la lista blanca: es como el
     * sistema escribe las metas protegidas.
     */
    public function setMeta($key, $value)
    {
        $this->metas()->updateOrCreate([
            'key' => $key
        ],[
            // Un arreglo se guarda como JSON, igual que en update_metas. Antes
            // llegaba tal cual a una columna de texto y fallaba al guardar.
            'value' => $this->parse_meta($value)
        ]);
        return $this;
    }

    public function setMetas(array $metas, ?string $foreignKey = null)
    {
        // No tener nada que escribir no es un error; antes lanzaba una excepción.
        if ($metas === []) {
            return $this;
        }

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
                'value' => $this->parse_meta($value),
            ];
        }

        // Validar que realmente se obtuvo la clave foránea
        if (!isset($data[0][$foreignKey])) {
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
	 * El camino del formulario: solo escribe las metas editables que no estén
	 * protegidas, y borra las que llegan vacías.
	 *
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
            // Se valida el valor tal como llegó. Antes se convertía a JSON
            // primero, así que un arreglo vacío pasaba a ser "[]" y nunca
            // borraba la meta.
            if ($this->validate_meta($meta)) {
                $valid_metas[] = [
                    'key' => $key,
                    $related => $this->getKey(),
                    'value' => $this->parse_meta($meta),
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
                                                ->where($related, $this->getKey())
                                                ->first();
                    event(new $event_class($new_meta));
                }
            }
        }

        // Eliminar metas inválidas
        if (!empty($metas_to_delete)) {
            $model_meta_class::whereIn('key', $metas_to_delete)
                ->where($related, $this->getKey())
                ->delete();
        }

        return $this;
    }

    /**
     * Lo que de la petición se puede escribir desde fuera: las claves de
     * `editable_metas` que no estén en `protected_metas`.
     */
    public function metas_array($metas)
    {
        // Sin lista blanca no entra nada. Antes se devolvía null, y update_metas
        // fallaba al recorrerlo.
        if (! isset($this->editable_metas)) {
            return [];
        }

        $editable_metas = array_diff($this->editable_metas, $this->protectedMetas());

        $metas_array = [];

        foreach ($this->parse_metas($metas) as $key => $value) {
            if (in_array($key, $editable_metas)) {
                $metas_array += [$key => $value];
            }
        }

        return $metas_array;
    }

    /**
     * Las metas que solo escribe el sistema.
     *
     * @return array<int, string>
     */
    public function protectedMetas(): array
    {
        return (isset($this->protected_metas) && is_array($this->protected_metas))
            ? $this->protected_metas
            : [];
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
