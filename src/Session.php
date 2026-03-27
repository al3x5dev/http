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
     * 
     * @param array $options Opciones de configuración de sesión
     * @return bool True si la sesión se inició correctamente
     **/
    public static function start(array $options = []): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $defaultOptions = [
                "use_cookies" => true,
                "use_only_cookies" => true,
                "cookie_lifetime" => 0,
                "cookie_httponly" => true,
                "cookie_secure" => true,
                "cookie_samesite" => "Strict",
                "use_strict_mode" => true,
            ];

            if (!empty($options)) {
                $options = self::validate($options);
                $result = session_start(array_merge($defaultOptions, $options));
            } else {
                $result = session_start($defaultOptions);
            }

            self::initFlash();

            return $result;
        }
        return false;
    }

    private static function initFlash(): void
    {
        if (!isset($_SESSION['_mk4u_flash'])) {
            $_SESSION['_mk4u_flash'] = [
                '_new' => [],
                '_old' => []
            ];
            return;
        }

        $_SESSION['_mk4u_flash']['_old']=[];

        if (isset($_SESSION['_mk4u_flash']['_new']) && !empty($_SESSION['_mk4u_flash']['_new'])) {
            $_SESSION['_mk4u_flash']['_old'] = $_SESSION['_mk4u_flash']['_new'];
            $_SESSION['_mk4u_flash']['_new'] = [];
        }
    }

    /**
     * Devuelve el valor de $_SESSION
     * 
     * @param string|null $name Nombre de la clave (null para obtener todo)
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Valor o array de valores
     */
    public static function get(?string $name = null, mixed $default = null): mixed
    {
        self::requireSession();
        if (is_null($name)) {
            return $_SESSION;
        }

        return self::has($name) ? $_SESSION[$name] : $default;
    }

    /**
     * Establece valores para $_SESSION
     * 
     * En caso de existir la session sobreescribe el valor
     * 
     * @param string $name Nombre de la clave
     * @param mixed $value Valor a guardar
     */
    public static function set(string $name, mixed $value): void
    {
        self::requireSession();
        $_SESSION[$name] = $value;
    }

    /**
     * Elimina valores para $_SESSION
     * 
     * @param string $name Nombre de la clave a eliminar
     */
    public static function remove(string $name): void
    {
        self::requireSession();
        unset($_SESSION[$name]);
    }

    /**
     * Alias de Session::remove()
     * 
     * @param string $name Nombre de la clave a eliminar
     */
    public static function delete(string $name): void
    {
        self::requireSession();
        self::remove($name);
    }

    /**
     * Verifica si existe una session dada
     * 
     * @param string $name Nombre de la clave
     * @return bool True si existe
     */
    public static function has(string $name): bool
    {
        self::requireSession();
        return isset($_SESSION[$name]);
    }

    /**
     * Verifica si esta activa la sesion
     */
    public static function active(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }


    /**
     * Verifica que la sesión esté activa y lanza excepción si no
     * 
     * @throws RuntimeException Si la sesión no está iniciada
     */
    private static function requireSession(): void
    {
        if (!self::active()) {
            throw new \RuntimeException('Session not started');
        }
    }

    /**
     * Obtiene y elimina un valor de la sesión
     * 
     * @param string $name Nombre de la clave
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Valor obtenido o default
     */
    public static function pull(string $name, mixed $default = null): mixed
    {
        self::requireSession();

        if (!self::has($name)) {
            return $default;
        }
        $value = $_SESSION[$name];
        unset($_SESSION[$name]);
        return $value;
    }

    /**
     * Elimina todos los datos de la sesión
     */
    public static function flush(): void
    {
        self::requireSession();
        $_SESSION = [];
    }

    /**
     * Genera un nuevo ID de session
     * 
     * @param bool $deleteOldSession Eliminar datos de la sesión antigua
     */
    public static function renewId(bool $deleteOldSession = false): void
    {
        self::requireSession();
        session_regenerate_id($deleteOldSession);
    }

    /**
     * Destruye la sesion con todos sus datos
     * 
     * @throws RuntimeException
     */
    public static function destroy(): void
    {
        self::requireSession();

        // Eliminar cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly'],
                //$params['samesite'] ?? 'Lax'
            );
        }

        session_unset();
        session_destroy();
    }

    /**
     * Devuelve el id de la session
     * 
     * @return string ID de sesión
     */
    public static function id(): string
    {
        self::requireSession();
        return session_id();
    }

    /**
     * Validar opciones de inicio
     * 
     * @param array $options Opciones a validar
     * @return array Opciones validadas y procesadas
     * @throws \RuntimeException Si alguna opción es inválida
     */
    private static function validate(array $options): array
    {
        foreach ($options as $key => $value) {
            if (!array_key_exists($key, self::CFG)) {
                throw new \RuntimeException(sprintf("'%s' is not a valid configuration parameter.", $key));
            }

            $expectedType = self::CFG[$key];
            if (gettype($value) !== $expectedType) {
                throw new \RuntimeException(sprintf("Expected data type '%s' for '%s,", $expectedType, $key));
            }

            if ($key === 'name') {
                $options[$key] = "_mk4u_$value";
            }
        }
        return $options;
    }

    // -------------- Flash Messages ---------------
    /**
     * Establece y muestra los flash message
     * 
     * @param string $name Nombre del mensaje flash
     * @param mixed $value Valor a mostrar (null para obtener)
     * @return mixed|null
     */
    public static function flash(string $name, mixed $value = null): ?string
    {
        self::requireSession();

        if (is_null($value)) {
            return self::getFlash($name);
        }
        self::setFlash($name, $value);
        return null;
    }
}
