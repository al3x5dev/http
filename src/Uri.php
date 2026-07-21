<?php

namespace Mk4U\Http;

use Uri\Rfc3986\Uri as Rfc3986Uri;

/**
 * Uri class
 */
class Uri
{
    private Rfc3986Uri $uri;
    private const  DEFAULT_PORTS = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    public function __construct(string $str = '')
    {
        $native = new Rfc3986Uri($str);

        // Obtener puerto
        $port = self::normalizedPort($native->getPort(), $native->getScheme() ?? '');
        $this->uri = $native->withPort($port);
    }

    /** 
     * Establece el esquema de la url
     */
    public function withScheme(string $scheme = ''): static
    {
        $new = clone $this;
        $new->uri = $this->uri->withScheme($scheme);
        return $new;
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
     * @param null|string $password La contraseña asociada con $usuario.
     * @return static Instancia con la información de usuario especificada.
     */
    public function withUserInfo(string $user, ?string $password = NULL): static
    {
        $userInfo = (!empty($password)) ? "$user:$password" : $user;
        $new = clone $this;
        $new->uri = $this->uri->withUserInfo($userInfo);
        return $new;
    }

    /** 
     * Establece el host de la url
     */
    public function withHost(string $host = ''): static
    {
        $new = clone $this;
        $new->uri = $this->uri->withHost($host);
        return $new;
    }

    /** 
     * Establece puerto
     */
    public function withPort(?int $port = NULL): static
    {
        $new = clone $this;
        $new->uri = $this->uri->withPort(
            self::normalizedPort(
                $port,
                $this->getScheme()
            )
        );
        return $new;
    }

    /** 
     * Establece la ruta de la url
     */
    public function withPath(string $path = '/'): static
    {
        $new = clone $this;
        $new->uri = $this->uri->withPath($path);
        return $new;
    }

    /** 
     * Establece las consultas de la url
     */
    public function withQuery(string $query = ''): static
    {
        $new = clone $this;
        $new->uri = $this->uri->withQuery($query);
        return $new;
    }

    /** 
     * Establece el fragmento de URI especificado
     */
    public function withFragment(string $fragment = ''): static
    {
        $new = clone $this;
        $new->uri = $this->uri->withFragment($fragment);
        return $new;
    }

    /** 
     * Recuperar el componente de esquema de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-3.1 
     */
    public function getScheme(): string
    {
        return $this->uri->getScheme() ?? '';
    }

    /** 
     * Recuperar el componente host del URI.
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2 
     */
    public function getHost(): string
    {
        return $this->uri->getHost() ?? '';
    }


    /** 
     * Recuperar el componente de puerto de la URI.
     */
    public function getPort(): ?int
    {
        return $this->uri->getPort();
    }

    /** 
     * Recuperar el componente de ruta del URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     */
    public function getPath(): string
    {
        return $this->uri->getPath();
    }

    /** 
     * Recuperar la cadena de consulta de la URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2 
     * @see https://tools.ietf.org/html/rfc3986#section-3.4 
     */
    public function getQuery(): string
    {
        return $this->uri->getQuery() ?? '';
    }

    /**
     * Devuelve una cadena de consultas como array
     * 
     * @see https://www.php.net/manual/es/function.parse-str.php
     */
    public function getQueryToArray(): array
    {
        parse_str($this->getQuery() ?? '', $array);
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
        return $this->uri->getFragment() ?? '';
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
        $authority = $this->getHost();
        if ($authority === '') {
            return '';
        }
        $userInfo = $this->getUserInfo();
        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }
        $port = $this->getPort();
        if ($port !== null) {
            $authority .= ':' . $port;
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
        return $this->uri->getUserInfo() ?? '';
    }

    // Obtener clave sin procesar
    public function getPassword(): ?string
    {
        return $this->uri->getRawPassword();
    }

    /**
     * Verifica si dos URIs son equivalentes
     * 
     * @param Uri $uri Objeto URI a comparar con la URI actual. 
     * @param bool $fragment Indica si el componente de fragmento se tiene en cuenta en la comparación. Por defecto se excluye
     */
    public function equals(Uri $uri, bool $fragment = false): bool
    {
        $comparisonMode = ($fragment)
            ? \Uri\UriComparisonMode::IncludeFragment
            : \Uri\UriComparisonMode::ExcludeFragment;

        return $this->uri->equals($uri->uri, $comparisonMode);
    }

    /** 
     * Devuelve la representación de la URI como texto. 
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.1 
     */
    public function __toString(): string
    {
        return $this->uri->toString();
    }

    /**
     * Normalizacion de puerto
     */
    private static function normalizedPort(?int $port = null, string $scheme = ''): ?int
    {
        if (!is_null($port) && ($port < 1 || $port > 65535)) {
            throw new \Uri\InvalidUriException(sprintf('Invalid port: %d. It must be between 1 and 65535', $port));
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
            'userInfo' => $this->getUserInfo(),
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'path' => $this->getPath(),
            'query' => $this->getQuery(),
            'fragment' => $this->getFragment(),
        ];
    }
}
