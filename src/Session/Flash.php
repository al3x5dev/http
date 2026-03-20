<?php

namespace Mk4U\Http\Session;

/**
 * Flash class
 */
trait Flash
{

    #    [
    #        '_mk4u_flash'=>[
    #            '_new'=>[],
    #            '_old'=>[]
    #        ]
    #    ];

    private static function setFlash(string $name, $value): void
    {
        $_SESSION['_mk4u_flash']['_new'][] = $name;
        self::set($name, $value);
    }

    private static function getFlash(string $name): ?string
    {
        $messages = $_SESSION['_mk4u_flash']['_new'] ?? [];
        foreach ($messages as $key => $value) {
            if ($value === $name) {
                $_SESSION['_mk4u_flash']['_old'][] = $name;
                $flash = self::get($value);
                unset($_SESSION['_mk4u_flash']['_new'][$key]);
                self::remove($value);
                break;
            }
        }

        return $flash ?? null;
    }
}
