<?php

namespace Innoboxrr\Traits;

use Closure;

trait DumpsGlobalScopes
{
    /**
     * Los scopes globales del modelo, por su clave, nombrando cada uno.
     *
     * @return array<string, string>
     */
    public static function dumpMyGlobalScopes(): array
    {
        $scopes = (new static())->getGlobalScopes();

        return array_map(static function ($scope): string {
            // Un Closure es un objeto, asi que preguntar primero por is_object
            // devolvia la cadena 'Closure' y la rama de los closures no se
            // alcanzaba nunca — que era justo lo que este metodo venia a
            // distinguir.
            if ($scope instanceof Closure) {
                return 'closure';
            }

            if (is_object($scope)) {
                return $scope::class;
            }

            return is_callable($scope) ? 'callable' : gettype($scope);
        }, $scopes);
    }
}
