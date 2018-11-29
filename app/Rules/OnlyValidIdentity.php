<?php
namespace App\Rules;

use Adldap\Laravel\Validation\Rules\Rule;

class OnlyValidIdentity extends Rule
{
    /**
     * Determines if the user is allowed to authenticate.
     *
     * @return bool
     */   
    public function isValid()
    {
        $dni = strtoupper(trim($this->user->getAccountName()));
        if (empty($dni)) {
            return false;
        }

        // cadena de caracteres que servirá para saber la letra que le debe corresponder
        // a un número de dni según el resto de dividir entre 23
        $cadenadni = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $letradni = substr($dni, strlen($dni) - 1, 1);  // letra final del dni
        // si no tiene uno de los formatos validos devuelve error
        if (preg_match('/^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$/', $dni) == 0 && preg_match('/^[A-Z]{1}[0-9]{8}[A-Z0-9]{1}$/', $dni) == 0 && preg_match('/^[0-9]{8}[A-Z]{1}$/', $dni) == 0) {
            return false;
        }

        // comprobacion de NIFs estandar
        if (preg_match('/^[0-9]{8}[A-Z]{1}$/', $dni) > 0) {
            $posicion = substr($dni, 0, 8) % 23;
            $letra = substr($cadenadni, $posicion, 1);
            if ($letra != $letradni) {
                return false;
            }
        }

        // si empieza por X, Y o Z
        if (preg_match('/^[XYZ]{1}/', $dni) > 0) {
            // reemplazamos la letra inicial X, Y o Z por 0, 1 o 2 respectivamente
            $temp = str_replace('X', '0', $dni);
            $temp = str_replace('Y', '1', $temp);
            $temp = str_replace('Z', '2', $temp);

            // longitud NIE puede ser 9 o 10, por lo tanto la longitud del número será 8 o 9 respectivamente
            $len = (strlen($dni) == 10) ? 9 : 8;
            $posicion = substr($temp, 0, $len) % 23;
            $letra = substr($cadenadni, $posicion, 1);
            if ($letradni != $letra) {
                return false;
            }
        }
        return true;
    }
}