<?php

namespace Mk4U\Http\Session;

/**
 * Flash class
 */
trait Flash
{
    /**
     * Establece un mensaje flash para el siguiente request
     * 
     * @param string $name Nombre del mensaje flash
     * @param mixed $value Valor a guardar
     */
    private static function setFlash(string $name, mixed $value): void
    {
        $_SESSION['_mk4u_flash']['_new'][$name] = $value;
    }

    /**
     * Obtiene y elimina un mensaje flash
     * 
     * @param string $name Nombre del mensaje flash
     * @return mixed|null Valor del flash o null
     */
    private static function getFlash(string $name): ?string
    {
        $flash = null;

        if (isset($_SESSION['_mk4u_flash']['_new'][$name])) {
            $flash = $_SESSION['_mk4u_flash']['_new'][$name];
            unset($_SESSION['_mk4u_flash']['_new'][$name]);
        } elseif (isset($_SESSION['_mk4u_flash']['_old'][$name])) {
            $flash = $_SESSION['_mk4u_flash']['_old'][$name];
            unset($_SESSION['_mk4u_flash']['_old'][$name]);
        }

        return $flash;
    }
}
