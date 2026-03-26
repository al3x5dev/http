<?php

namespace Mk4U\Http;

/**
 * Cookie class
 */
class Cookie
{
    /**
     * Agrega la cookie antes de enviarla al navegador
     * 
     * @param string $name Nombre de la cookie
     * @param mixed $value Valor de la cookie
     * @param int $expires Tiempo de vida en segundos (0 = hasta cerrar navegador)
     * @param string $path Ruta de la cookie
     * @param string $domain Dominio de la cookie
     * @param bool $secure Solo enviar por HTTPS
     * @param bool $httponly Accesible solo por HTTP (no JavaScript)
     * @param string $sameSite SameSite attribute (Strict, Lax, None)
     * @return bool True si se estableció la cookie
     */
    public static function set(
        string $name,
        mixed $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true,
        string $sameSite = 'Lax'
    ): bool {
        // Validar nombre de cookie
        if (empty($name) || preg_match('/[\x00-\x1F\x7F]/', $name)) {
            throw new \InvalidArgumentException('Invalid cookie name');
        }

        // Convertir valor a string
        $value = (string) $value;

        // Calcular tiempo de expiración
        $expires = $expires !== 0 ? time() + $expires : 0;

        // SameSite debe ser Strict, Lax o None
        if (!in_array($sameSite, ['Strict', 'Lax', 'None'], true)) {
            $sameSite = 'Lax';
        }

        // SameSite=None requiere Secure=true
        if ($sameSite === 'None' && !$secure) {
            $secure = true;
        }

        // Construir opciones para setcookie
        $options = [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $sameSite,
        ];

        return setcookie($name, $value, $options);
    }

    /**
     * Obtiene valores de $_COOKIE
     * 
     * @param string|null $name Nombre de la cookie (null para obtener todas)
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Valor de la cookie o array de todas las cookies
     */
    public static function get(?string $name = null, mixed $default = null): mixed
    {
        if (is_null($name)) {
            return $_COOKIE;
        }

        return self::has($name) ? $_COOKIE[$name] : $default;
    }

    /**
     * Obtiene valores escapados
     * 
     * @param string|null $name Nombre de la cookie (null para obtener todas)
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Valor de la cookie o array de todas las cookies
     */
    public static function escaped(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return array_map(fn($v) => is_string($v) ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : $v, $_COOKIE);
        }

        $value = self::get($name, $default);
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '';
    }

    /**
     * Verifica que exista la cookie
     * 
     * @param string $name Nombre de la cookie
     * @return bool True si existe
     */
    public static function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Elimina una cookie de forma segura
     * 
     * @param string $name Nombre de la cookie
     * @param string $path Ruta de la cookie
     * @param string $domain Dominio de la cookie
     * @param bool $secure Solo eliminar por HTTPS
     * @param bool $httponly La cookie era HttpOnly
     * @return bool True si se eliminó
     */
    public static function remove(
        string $name,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httponly = true,
        string $sameSite = 'Lax'
    ): bool {
        // Usar expires en el pasado para eliminar
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $sameSite,
        ]);
    }
}
