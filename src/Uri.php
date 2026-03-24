<?php

namespace Mk4U\Http;

use Psr\Http\Message\UriInterface;

/**
 * Uri class
 */
class Uri implements UriInterface
{
    private const  DEFAULT_PORTS = [
        'http'  => 80,
        'https' => 443,
    ];

    public function __construct(
        private string $scheme = '',
        private readonly string $userInfo = '',
        private string $host = '',
        private ?int $port = NULL,
        private readonly string $path = '',
        private readonly string $query = '',
        private readonly string $fragment = ''
    ) {
        $this->scheme = self::normalized($scheme);
        $this->host = self::normalized($host);
        $this->port = self::normalizedPort($port, $this->scheme);
    }

    /** 
     * Establece el esquema de la url
     */
    public function withScheme(string $scheme = ''): UriInterface
    {
        $new = new static(
            $scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment
        );

        return $new;
    }

    /**
     * Devuelve una instancia con la información del usuario especificada.
     *
     * Este método DEBE conservar el estado de la instancia actual y devolver
     * una instancia que contiene la información del usuario especificada.
     *
     * La contraseña es opcional, pero la información del usuario DEBE incluir el
     * usuario; una cadena vacía para el usuario equivale a eliminar al usuario
     * información.
     *
     * @param string $user El nombre de usuario que se utilizará para obtener autoridad.
     * @param null|string $contraseña La contraseña asociada con $usuario.
     * @return static Una nueva instancia con la información de usuario especificada.
     */
    public function withUserInfo(string $user, ?string $password = NULL): UriInterface
    {
        $userInfo = self::userInfo($user, $password);

        $new = new static(
            $this->scheme,
            $userInfo,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment
        );

        return $new;
    }

    /** 
     * Establece el host de la url
     */
    public function withHost(string $host = ''): UriInterface
    {
        $new = new static(
            $this->scheme,
            $this->userInfo,
            $host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment
        );

        return $new;
    }

    /** 
     * Establece puerto
     */
    public function withPort(?int $port = NULL): UriInterface
    {
        $new = new static(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $port,
            $this->path,
            $this->query,
            $this->fragment
        );

        return $new;
    }

    /** 
     * Establece la ruta de la url
     * 
     * Si la ruta contiene parametros de consulta los envia a setQuery()
     */
    public function withPath(string $path = '/'): UriInterface
    {
        $new = new static(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $path,
            $this->query,
            $this->fragment
        );

        return $new;
    }

    /** 
     * Establece las consultas de la url
     */
    public function withQuery(string $query = ''): UriInterface
    {
        $new = new static(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $this->path,
            $query,
            $this->fragment
        );

        return $new;
    }

    /** 
     * Establece el fragmento de URI especificado
     */
    public function withFragment(string $fragment = ''): UriInterface
    {
        $new = new static(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $fragment
        );

        return $new;
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
     * Si no hay información de autoridad presente, este método DEBE devolver un valor vacío cadena.
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
     * cadena.
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
        return (isset($password)) ? "$user:$password" : $user;;
    }

    /**
     * Establece URI desde una string
     */
    public static function fromString(string $uri): UriInterface
    {
        if ($uri !== '') {
            if (empty($parts = parse_url($uri))) {
                //Unable to parse URI
                throw new \InvalidArgumentException("Unable to parse URI");
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
        if (!is_null($port) &&($port < 1 || $port > 65535)) {
            throw new \InvalidArgumentException(sprintf('Invalid port: %d. It must be between 0 and 65535', $port));
        }

        if ($scheme == '' && empty($port)) {
            return null;
        }

        $default = self::DEFAULT_PORTS[$scheme];
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
            'user-info' => $this->userInfo,
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'path' => $this->getPath(),
            'query' => $this->getQuery(),
            'fragment' => $this->getFragment(),
        ];
    }
}
