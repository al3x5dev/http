<?php

namespace Mk4U\Http;

/**
 * Request class
 */
class Request
{
    /** @param array $files datos de carga de archivos*/
    private array $files;

    /** @param string $method Metodo HTTP*/
    private string $method;

    /** @param Uri $uri instancia de la clase Mk4u\Http\Uri */
    private Uri $uri;

    /** @param array $form_content_type datos pasados por formulario(POST) */
    private array $form_content_type = ['application/x-www-form-urlencoded', 'multipart/form-data'];

    /** @param mixed $content Contenido de la solicitud HTTP */
    private mixed $content = null;

    use Headers;

    /**
     * Crea un nuevo objeto Request
     */
    public function __construct(
        string $method,
        string|Uri $uri,
        array $headers = [],
        $body = null,
        ?string $version = null
    ) {
        //metodo
        $this->setMethod($method);

        // URI
        $this->setUri(
            $uri instanceof Uri ? $uri : new Uri($uri)
        );

        //Headers
        $this->setHeaders($headers);

        //Content
        $this->content = $body;

        //Vesion Http
        $this->setProtocolVersion($version);
    }

    /**
     * Debuguear solicitud HTTP
     */
    public function __debugInfo(): array
    {
        return [
            "method" => $this->getMethod(),
            "uri" => $this->getUri(),
            "protocol" => $this->getProtocolVersion(),
            "headers" => $this->getHeaders(),
            "content" => $this->content
        ];
    }

    /**
     * Crea un nuevo objeto Request a partir de las superglobales
     */
    public static function create(): static
    {
        $uri = self::createUri();
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        $request = new static(
            self::server('request_method','GET'),
            $uri,
            $headers
        );

        $request->getContent();

        return $request;
    }

    /**
     * Crea un objeto Uri a partir del array $_SERVER
     */
    private static function createUri(): Uri
    {
        $server = self::server();

        [$user, $pass] = self::fetchUserInfo($server);

        $uri = (new Uri())
            ->withScheme(self::fetchScheme($server))
            ->withHost(self::fetchHost($server))
            ->withPort(self::fetchPort($server))
            ->withPath(self::fetchPath($server))
            ->withQuery(self::fetchQuery($server));

        if ($user !== null) {
            $uri = $uri->withUserInfo($user, $pass);
        }

        return $uri;
    }

    /**
     * Devuelve parametros del $_SERVER.
     */
    public static function server(?string $index = null, mixed $default = null, bool $all = false): mixed
    {
        if ($all || $index === null) {
            return $_SERVER;
        }

        return $_SERVER[strtoupper($index)] ?? $default;
    }

    /**
     * Obtiene Ip del cliente
     */
    public static function getClientIp(): string
    {
        return self::server('HTTP_CLIENT_IP')
            ?? self::server('HTTP_X_FORWARDED_FOR')
            ?? self::server('HTTP_X_REAL_IP')
            ?? self::server('REMOTE_ADDR')
            ?? '0.0.0.0';
    }

    /**
     * Obtener solicitud de destino
     * 
     * @see http://tools.ietf.org/html/rfc7230#section-5.3
     */
    public function getTarget(): string
    {
        $target = $this->uri->getPath();
        return ($target !== '') ? $target : '/';
    }

    /**
     * Obtener metodo http
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Establecer metodo http
     */
    public function setMethod(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Verificar metodo http
     */
    public function hasMethod(string $method): bool
    {
        return strcasecmp($this->method, $method) === 0;
    }

    /**
     * Obtener Uri
     */
    public function getUri(): Uri
    {
        return $this->uri;
    }

    /**
     * Establecer Uri
     */
    public function setUri(Uri $uri, bool $preserveHost = false): static
    {
        $this->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('host') || $this->getHeaderLine('host') === '') {
            $this->setHeader('host', $uri->getHost());
        }

        return $this;
    }

    /**
     * Obtener cuerpo del mensaje HTTP
     */
    private function getContent(): void
    {
        //contenido
        if (
            in_array($this->getMethod(), ['PUT', 'DELETE', 'PATCH'], true)
            ||
            ($this->hasMethod('POST') && !$this->isFormData())
        ) {
            $this->content = file_get_contents('php://input');
        }

        //archivos
        if ($this->isFormData() && $_FILES) {
            $this->normalizeFiles($_FILES);
        }
    }

    /**
     * Determina si los valores son pasados a traves de un formulario
     */
    public function isFormData(): bool
    {
        $content_type = explode(';', $this->getHeaderLine('content-type'))[0];
        return ($this->hasMethod('POST') && in_array($content_type, $this->form_content_type, true));
    }

    /**
     * Obtener parámetros
     *
     * En caso de no especificar el parametro a devolver, devuelve todos los valores 
     * del $params propiedad. 
     * 
     * Puede agregarle valores por defecto en caso de 
     * que $params[$name] no este definido.
     **/
    private function params(array $params, ?string $name = null, mixed $default = null): mixed
    {
        if (empty($name)) {
            return $params;
        }

        return $params[$name] ?? $default;
    }

    /**
     * Obtener parámetros en la cadena de consulta de la URI
     *
     * En caso de no especificar el parametro a devolver este metodo 
     * devuelve todos los valores de la superglobal $_GET.
     * 
     * Puede agregarle valores a $_GET especificando 
     * el nombre del parametro y el valor.
     * 
     * Tenga en cuenta que funciona para todas las solicitudes con una cadena de consulta.
     **/
    public function queryData(?string $name = null, mixed $default = null): mixed
    {
        return $this->params($_GET, $name, $default);
    }

    /**
     * Recuperar los parámetros proporcionados en el cuerpo de la solicitud.
     *
     * Si el tipo de contenido de la solicitud es application/x-www-form-urlencoded
     * o multipart/form-data, y el método de solicitud es POST, este método DEBE
     * devolver el contenido de $_POST.
     *
     * De lo contrario, este método puede devolver cualquier resultado de deserializar
     * el contenido del cuerpo de la solicitud; como el análisis devuelve contenido estructurado, el
     * los tipos potenciales DEBEN ser matrices u objetos solamente. Un valor nulo indica
     * la ausencia de contenido corporal.
     **/
    public function inputData(?string $name = null, mixed $default = null): mixed
    {
        if ($this->isFormData()) {
            return $this->params($_POST, $name, $default);
        }

        //Si hay contenido en la propiedad content, intentamos deserializarlo
        if ($this->content !== null) {
            parse_str($this->content, $output);
            return $this->params($output, $name, $default);
        }

        //Si no hay contenido, devolvemos null o el valor por defecto
        return $default;
    }

    /**
     * Devuelve JSON decodificado
     */
    public function jsonData(bool $assoc = true): array|object|null
    {
        if (str_contains($this->getHeaderLine('content-type'), 'application/json')) {
            return json_decode($this->content, $assoc, flags: JSON_THROW_ON_ERROR);
        }
        return null;
    }

    /**
     * Devuelve el cuerpo de la solicitud sin tratar
     */
    public function rawData(): ?string
    {
        return $this->content;
    }

    /**
     * Obtiene ficheros subidos al servidor
     */
    public function files(): array
    {
        return $this->files ?? [];
    }

    private static function fetchScheme(array $server): string
    {
        if (!empty($server['HTTPS']) && filter_var($server['HTTPS'], FILTER_VALIDATE_BOOLEAN)) {
            return 'https';
        }
        return 'http';
    }

    private static function fetchHost(array $server): string
    {
        if (!empty($server['HTTP_HOST'])) {
            return preg_replace('/:\d+$/', '', $server['HTTP_HOST']);
        }
        return $server['SERVER_NAME'] ?? 'localhost';
    }

    private static function fetchPort(array $server): ?int
    {
        if (!empty($server['HTTP_HOST']) && preg_match('/:(\d+)$/', $server['HTTP_HOST'], $m)) {
            return (int) $m[1];
        }
        if (!empty($server['SERVER_PORT'])) {
            return (int) $server['SERVER_PORT'];
        }
        return null;
    }

    private static function fetchPath(array $server): string
    {
        $path = $server['REQUEST_URI'] ?? $server['PHP_SELF'] ?? '/';
        $path = parse_url($path, PHP_URL_PATH);
        return $path !== false && $path !== null ? $path : '/';
    }

    private static function fetchQuery(array $server): string
    {
        if (!empty($server['QUERY_STRING'])) {
            return $server['QUERY_STRING'];
        }
        if (!empty($server['REQUEST_URI'])) {
            $parts = explode('?', $server['REQUEST_URI'], 2);
            return $parts[1] ?? '';
        }
        return '';
    }

    private static function fetchUserInfo(array $server): array
    {
        $user = $server['PHP_AUTH_USER'] ?? null;
        $pass = $server['PHP_AUTH_PW'] ?? null;

        if (
            !empty($server['HTTP_AUTHORIZATION'])
            && str_starts_with(strtolower($server['HTTP_AUTHORIZATION']), 'basic')
        ) {
            $decoded = base64_decode(substr($server['HTTP_AUTHORIZATION'], 6), true);
            if ($decoded !== false) {
                $parts = explode(':', $decoded, 2);
                $user = $parts[0];
                $pass = $parts[1] ?? null;
            }
        }

        return [$user, $pass];
    }

    /**
     * Crea una instancia del objeto UploadedFile
     */
    private static function createUploadedFile(array $value): UploadedFile
    {
        return new UploadedFile(
            $value["tmp_name"],
            $value["size"],
            $value["name"],
            $value["type"],
            $value["error"]
        );
    }

    /**
     * Normaliza archivos enviados por $_FILES
     */
    private function normalizeFiles(array $uploadFiles): void
    {
        //archivos
        foreach ($uploadFiles as $key => $file) {
            if (is_array($file['name'])) {
                foreach ($file['name'] as $i => $name) {
                    $this->files[$key][] = self::createUploadedFile([
                        'name'     => $file['name'][$i]      ?? null,
                        'type'     => $file['type'][$i]      ?? null,
                        'tmp_name' => $file['tmp_name'][$i]  ?? null,
                        'error'    => $file['error'][$i]     ?? null,
                        'size'     => $file['size'][$i]      ?? null,
                    ]);
                }
            } else {
                $this->files[$key] = self::createUploadedFile($file);
            }
        }
    }
}
