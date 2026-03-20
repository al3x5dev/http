<?php

namespace Mk4U\Http;

use Mk4U\Http\Session\Flash;

/**
 * Session class
 */
class Session
{
    private const CFG = [
        "auto_start" => "boolean",
        "cache_expire" => "integer",
        "cache_limiter" => "string",
        "cookie_domain" => "string",
        "cookie_httponly" => "boolean",
        "cookie_lifetime" => "integer",
        "cookie_path" => "string",
        "cookie_samesite" => "string",
        "cookie_secure" => "boolean",
        "gc_divisor" => "integer",
        "gc_maxlifetime" => "integer",
        "gc_probability" => "integer",
        "lazy_write" => "boolean",
        "name" => "string",
        "referer_check" => "string",
        "save_handler" => "string",
        "save_path" => "string",
        "serialize_handler" => "string",
        "sid_bits_per_character" => "integer",
        "sid_length" => "integer",
        "trans_sid_hosts" => "string",
        "trans_sid_tags" => "string",
        "use_cookies" => "boolean",
        "use_only_cookies" => "boolean",
        "use_strict_mode" => "boolean",
        "use_trans_sid" => "boolean",
    ];

    use Flash;

    /**
     * Inicializa la session
     **/
    public static function start(array $options = []): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (!empty($options)) {
                self::validate($options);
                return session_start($options);
            }

            return session_start([
                "name" => "_mk4u_" . bin2hex(random_bytes(8)),
                "use_cookies" => true,
                "use_only_cookies" => true,
                "cookie_lifetime" => 0,
                "cookie_httponly" => true,
                "cookie_secure" => true,
                "use_strict_mode" => true,
            ]);
        }
        return false;
    }

    /**
     * Devuelve el valor de $_SESSION
     */
    public static function get(?string $name = null, mixed $default = null): mixed
    {
        if (is_null($name)) {
            return $_SESSION;
        }

        return self::has($name) ? $_SESSION[$name] : $default;
    }

    /**
     * Establece valores para $_SESSION
     * 
     * En caso de existir la session sobreescribe el valor
     */
    public static function set(string $name, mixed $value): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }

        $_SESSION[$name] = $value;
    }

    /**
     * Elimina valores para $_SESSION
     */
    public static function remove(string $name): void
    {
        unset($_SESSION[$name]);
    }

    /**
     * Alias de Session::remove()
     */
    public static function delete(string $name): void
    {
        self::remove($name);
    }

    /**
     * Verifica si existe una session dada
     */
    public static function has(string $name): bool
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Genera un nuevo ID de session
     */
    public static function renewId(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Destruye la sesion con todos sus datos
     */
    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException();
        }
        session_unset();
        session_destroy();
    }

    /**
     * Devuelve el id de la session
     */
    public static function id(): string
    {
        return session_id();
    }

    /**
     * Validar opciones de inicio
     */
    private static function validate(array $options): void
    {
        foreach ($options as $key => $value) {
            if (!array_key_exists($key, self::CFG)) {
                throw new \RuntimeException(sprintf("'%s' is not a valid configuration parameter.", $key));
            }

            $expectedType = self::CFG[$key];
            if (gettype($value) !== $expectedType) {

                throw new \RuntimeException(sprintf("Expected data type '%s' for '%s,", $expectedType, $key));
            }
        }
    }

    // -------------- Flash Messages ---------------
    /**
     * Establece y muestra los flash message
     */
    public static function flash(string $name, mixed $value = null): ?string
    {
        if (empty($value)) {
            return self::getFlash($name);
        }
        self::setFlash($name, $value);
        return null;
    }
}
