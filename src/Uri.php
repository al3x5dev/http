<?php

namespace Mk4U\Http;


/**
 * Uri class
 */
class Uri
{
    private const  DEFAULT_PORTS = [
        'http'  => 80,
        'https' => 443,
    ];

    public function __construct(
        private string $scheme = '',
        private string $userInfo = '',
        private string $host = '',
        private ?int $port = NULL,
        private string $path = '',
        private string $query = '',
        private string $fragment = ''
    ) {
        $this->scheme = self::normalized($scheme);
        $this->host = self::normalized($host);
        $this->port = self::normalizedPort($port, $this->scheme);
    }

    /** 
     * Establece el esquema de la url
     */
    public function setScheme(string $scheme = ''): Uri
    {
        $this->scheme = self::normalized($scheme);
        return $this;
    }

    /**
     * Devuelve una instancia con la información del usuario especificada.
     *
     * Este método DEBE devolver la información del usuario especificada.
     *
     * La contraseña es opcional, pero la información del usuario DEBE incluir el
     * usuario; una cadena vacía para el usuario equivale a eliminar al usuario
     * información.
     *
     * @param string $user El nombre de usuario que se utilizará para obtener autoridad.
     * @param null|string $contraseña La contraseña asociada con $usuario.
     * @return static Instancia con la información de usuario especificada.
     */
    public function setUserInfo(string $user, ?string $password = NULL): Uri
    {
        $userInfo = self::userInfo($user, $password);
        $this->userInfo = $userInfo;
        return $this;
    }

    /** 
     * Establece el host de la url
     */
    public function setHost(string $host = ''): Uri
    {
        $this->host = self::normalized($host);
        return $this;
    }

    /** 
     * Establece puerto
     */
    public function setPort(?int $port = NULL): Uri
    {
        $this->port = self::normalizedPort($port, $this->scheme);
        return $this;
    }

    /** 
     * Establece la ruta de la url
     * 
     * Si la ruta contiene parametros de consulta los envia a setQuery()
     */
    public function setPath(string $path = '/'): Uri
    {
        $this->path = $path;
        return $this;
    }

    /** 
     * Establece las consultas de la url
     */
    public function setQuery(string $query = ''): Uri
    {
        $this->query = $query;
        return $this;
    }

    /** 
     * Establece el fragmento de URI especificado
     */
    public function setFragment(string $fragment = ''): Uri
    {
        $this->fragment = $fragment;
        return $this;
    }

    /** 
     * Recuperar el componente de esquema de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-3.1 
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /** 
     * Recuperar el componente host del URI.
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2 
     */
    public function getHost(): string
    {
        return $this->host;
    }


    /** 
     * Recuperar el componente de puerto de la URI.
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /** 
     * Recuperar el componente de ruta del URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /** 
     * Recuperar la cadena de consulta de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.4 
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Devuelve una cadena de consultas como array
     * 
     * @see https://www.php.net/manual/es/function.parse-str.php
     */
    public function getQueryToArray(): array
    {
        parse_str($this->query, $array);
        return $array;
    }

    /** 
     * Recuperar el componente de fragmento de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.5 
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * Recuperar el componente de autoridad del URI.
     *
     * Si no hay información de autoridad presente, este método DEBE devolver un valor vacío.
     *
     * La sintaxis de autoridad del URI es:
     *
     * <pre>
     * [información-usuario@]host[:puerto]
     * </pre>
     *
     * Si el componente del puerto no está configurado o es el puerto estándar para el actual
     * esquema, NO DEBE incluirse.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string La autoridad URI, en formato "[user-info@]host[:port]".
     */
    public function getAuthority(): string
    {
        $authority = $this->host;

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->getPort() !== null) {
            $authority .= ':' . $this->getPort();
        }

        return $authority;
    }

    /**
     * Recuperar el componente de información del usuario del URI.
     *
     * Si no hay información del usuario presente, este método DEBE devolver un valor vacío
     *
     * Si un usuario está presente en la URI, esto devolverá ese valor;
     * Además, si la contraseña también está presente, se agregará al
     * valor de usuario, con dos puntos (":") separando los valores.
     *
     * El carácter "@" final no forma parte de la información del usuario y NO DEBE
     * agregarce.
     *
     * @return string La información del usuario URI, en formato "nombre de usuario[:contraseña]".
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /** 
     * Devuelve la representación de la URI como texto. 
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.1 
     */
    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . '://';
        }

        $uri .= $this->getAuthority() . $this->path;
        if (!empty($this->query)) $uri .= '?' . $this->query;
        if (!empty($this->fragment)) $uri .= '#' . $this->fragment;

        return $uri;
    }

    /**
     * Normalizar a minusculas cadena de caracteres
     */
    private static function userInfo(string $user, ?string $password = NULL): string
    {
        return (isset($password)) ? "$user:$password" : $user;
    }

    /**
     * Establece URI desde una string
     */
    public static function fromString(string $uri): Uri
    {
        $parts = [];

        if ($uri !== '') {
            $parts = parse_url($uri);
            if (empty($parts)) {
                //Unable to parse URI
                throw new \InvalidArgumentException("Unable to parse URI");
            }

            // Fix: si no hay scheme, no hay host, y el path parece dominio (no empieza con /), moverlo a host
            if (
                empty($parts['scheme']) &&
                empty($parts['host']) &&
                !empty($parts['path']) &&
                !str_starts_with($parts['path'], '/')
            ) {
                // Si el path contiene /, separar dominio del path
                if (strpos($parts['path'], '/') !== false) {
                    $pathParts = explode('/', $parts['path'], 2);
                    $potentialHost = $pathParts[0];
                    $pathPart = '/' . $pathParts[1];
                } else {
                    $potentialHost = $parts['path'];
                    $pathPart = '';
                }

                // Verificar si es un dominio válido
                if (filter_var($potentialHost, FILTER_VALIDATE_DOMAIN)) {
                    $parts['host'] = $potentialHost;
                    $parts['path'] = $pathPart;
                }
            }
        }

        return new static(
            $parts['scheme'] ?? '',
            self::userInfo($parts['user'] ?? '', $parts['pass'] ?? null),
            $parts['host'] ?? '',
            $parts['port'] ?? null,
            $parts['path'] ?? '',
            $parts['query'] ?? '',
            $parts['fragment'] ?? ''
        );
    }

    /**
     * Normalizacion a caracteres en minuscula
     */
    private static function normalized(string $str = ''): string
    {
        return strtolower($str);
    }

    /**
     * Normalizacion de puerto
     */
    private static function normalizedPort(?int $port = null, string $scheme = ''): ?int
    {
        if (!is_null($port) && ($port < 1 || $port > 65535)) {
            throw new \InvalidArgumentException(sprintf('Invalid port: %d. It must be between 1 and 65535', $port));
        }

        if ($scheme === '' && is_null($port)) {
            return null;
        }

        $default = self::DEFAULT_PORTS[$scheme] ?? '';
        if (!empty($default) && $default === $port) {
            return null;
        }

        return $port;
    }
    /** 
     * Devuelve la representación de la URI como array. 
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.1 
     */
    public function __debugInfo(): array
    {
        return [
            'scheme' => $this->getScheme(),
            'userInfo' => $this->userInfo,
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'path' => $this->getPath(),
            'query' => $this->getQuery(),
            'fragment' => $this->getFragment(),
        ];
    }
}
