<?php

namespace Mk4U\Http;

/**
 * Cookie class
 */
class Cookie
{
    /**
     * Agrega la cookie antes de enviarla al navegador
     */
    public static function set(
        string $name,
        mixed $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = false
    ): bool {
        if ($expires != 0) {
            $expires = time() + $expires;
        }
        return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }

    /**
     * Obtiene valores de $_COOKIE
     */
    public static function get(?string $name = null, mixed $default = null): mixed
    {
        if (is_null($name)) {
            return $_COOKIE;
        }

        return self::has($name) ? $_COOKIE[$name] : $default;
    }

    /**
     * Verifica que exista la cookie
     */
    public static function has(string $name): bool
    {
        return key_exists($name, $_COOKIE);
    }

    /**
     * Elimina una cookie
     */
    public  static function remove(
        string $name,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = false
    ): void {
        self::set($name, '', -1, $path, $domain, $secure, $httponly);
    }
}
