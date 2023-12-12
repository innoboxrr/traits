<?php

namespace Innoboxrr\Traits;

trait SemVerOperation
{

    /**
     * Incrementa la versión SemVer según el tipo especificado.
     *
     * @param string $currentVersion La versión actual.
     * @param string $type El tipo de incremento ('major', 'minor', 'patch').
     * @return string La versión incrementada.
     */
    public function incrementVersion($currentVersion, $type)
    {
        // Descomponer la versión actual en sus componentes
        $parts = explode('.', $currentVersion);

        if (count($parts) !== 3) {
            throw new Exception("La versión actual no es válida: " . $currentVersion);
        }

        list($major, $minor, $patch) = $parts;

        // Incrementar la versión según el tipo
        switch ($type) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;

            case 'minor':
                $minor++;
                $patch = 0;
                break;

            case 'patch':
                $patch++;
                break;

            default:
                throw new Exception("Tipo de incremento desconocido: " . $type);
        }

        // Reconstruir y devolver la versión incrementada
        return implode('.', [$major, $minor, $patch]);
    }

    /**
     * Decrementa la versión SemVer según el tipo especificado.
     *
     * @param string $currentVersion La versión actual.
     * @param string $type El tipo de decremento ('major', 'minor', 'patch').
     * @return string La versión decrementada.
     */
    public function decrementVersion($currentVersion, $type)
    {
        // Descomponer la versión actual en sus componentes
        $parts = explode('.', $currentVersion);

        if (count($parts) !== 3) {
            throw new Exception("La versión actual no es válida: " . $currentVersion);
        }

        list($major, $minor, $patch) = $parts;

        // Decrementar la versión según el tipo
        switch ($type) {
            case 'major':
                if ($major > 0) {
                    $major--;
                }
                $minor = 0;
                $patch = 0;
                break;

            case 'minor':
                if ($minor > 0) {
                    $minor--;
                } else if ($major > 0) {
                    $major--;
                    $minor = 0; // Aquí puedes establecer el valor máximo para 'minor' si es necesario
                }
                $patch = 0;
                break;

            case 'patch':
                if ($patch > 0) {
                    $patch--;
                } else if ($minor > 0) {
                    $minor--;
                    $patch = 0; // Aquí puedes establecer el valor máximo para 'patch' si es necesario
                } else if ($major > 0) {
                    $major--;
                    $minor = 0;
                    $patch = 0; // Aquí puedes establecer el valor máximo para 'patch' si es necesario
                }
                break;

            default:
                throw new Exception("Tipo de decremento desconocido: " . $type);
        }

        // Reconstruir y devolver la versión decrementada
        return implode('.', [$major, $minor, $patch]);
    }

    /**
     * Compara dos versiones SemVer.
     *
     * @param string $version1 La primera versión para comparar.
     * @param string $version2 La segunda versión para comparar.
     * @return int Retorna 0 si son iguales, -1 si la primera es menor, y 1 si la primera es mayor.
     */
    public function compareVersions($version1, $version2)
    {
        $a = explode('.', $version1);
        $b = explode('.', $version2);

        for ($i = 0; $i < 3; $i++) {
            if ($a[$i] < $b[$i]) {
                return -1;
            } elseif ($a[$i] > $b[$i]) {
                return 1;
            }
        }

        return 0; // Las versiones son iguales
    }

    /**
     * Valida si una cadena cumple con el formato SemVer.
     *
     * @param string $version La versión a validar.
     * @return bool Retorna true si la versión es válida, false de lo contrario.
     */
    public function isValidVersion($version)
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
    }
}
