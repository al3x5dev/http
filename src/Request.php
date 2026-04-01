<?php

namespace Mk4U\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Request class
 */
class Request extends Message implements RequestInterface
{
    /** @param array datos de carga de archivos*/
    private array $files;

    /** @param string Metodo HTTP*/
    private string $method;

    /** @param Uri instancia de la clase Mk4u\Http\Uri */
    private Uri $uri;

    /** @param array datos pasados por formulario(POST) */
    private array $form_content_type = ['application/x-www-form-urlencoded', 'multipart/form-data'];

    /** @param mixed $content Contenido de la solicitud HTTP */
    private mixed $content;

    /** @param array $output Datos parseados del cuerpo del mensaje*/
    private ?array $output = null;


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
        parent::__construct();

        // Version HTTP
        $this->version = !is_null($version) ? "HTTP/$version" : 'HTTP/1.1';

        // Metodo
        $this->method = strtoupper($method);

        // URI
        if ($uri instanceof Uri) {
            $this->uri = $uri;
        } else {
            $this->uri = Uri::fromString($uri);
        }

        // Headers
        $this->headers = $headers;

        // Content
        $this->content = $body;
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
    public static function create(): RequestInterface
    {
        //URI
        $uri = (new Uri())
            ->withScheme(self::server('request_scheme'))
            ->withHost(self::server('http_host'))
            ->withPort(self::server('server_port'))
            ->withPath(self::server('request_uri'))
            ->withQuery(self::server('query_string'));

        $request = new static(
            self::server('request_method'),
            $uri,
            getallheaders()
        );

        //Content
        $request->getContent();

        return $request;
    }

    /**
     * Devuelve parametros del $_SERVER.
     */
    public static function server(string $index = ''): array|string
    {
        return empty($index) ? $_SERVER : ($_SERVER[strtoupper($index)] ?? '');
    }

    /**
     * Obtener solicitud de destino
     * 
     * @see http://tools.ietf.org/html/rfc7230#section-5.3 (para los diversos
     * formularios de destino de solicitud permitidos en mensajes de solicitud)
     */
    public function getTarget(): string
    {
        $target = $this->uri->getPath();
        return ($target !== '') ? $target : '/';
    }

    /**
     * Obtiene el objetivo de la solicitud según PSR-7
     * 
     * @return string El objetivo de la solicitud
     */
    public function getRequestTarget(): string
    {
        return $this->getTarget();
    }

    /**
     * Devuelve una instancia con el objetivo de solicitud especificado
     * 
     * @param string $requestTarget El objetivo de la solicitud
     * @return static Nueva instancia con el objetivo especificado
     */
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $new = clone $this;
        $new->uri = $this->uri->withPath($requestTarget);
        return $new;
    }

    /**
     * Obtener metodo http
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Devuelve una instancia con el método especificado
     * 
     * @param string $method Metodo HTTP
     * @return static Nueva instancia con el metodo especificado
     */
    public function withMethod(string $method): RequestInterface
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    /**
     * Verificar metodo http
     */
    public function hasMethod(string $method): bool
    {
        return (strcasecmp($this->method, $method) == 0);
    }

    /**
     * Obtener Uri
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Devuelve una instancia con la URI especificada
     * 
     * @param UriInterface $uri Nueva URI
     * @param bool $preserveHost Si true, preserva el host original
     * @return static Nueva instancia con la URI especificada
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        
        if ($uri instanceof Uri) {
            $new->uri = $uri;
        } else {
            $new->uri = new Uri(
                $uri->getScheme(),
                $uri->getUserInfo(),
                $uri->getHost(),
                $uri->getPort(),
                $uri->getPath(),
                $uri->getQuery(),
                $uri->getFragment()
            );
        }

        if (!$preserveHost) {
            $new->headers['Host'] = $uri->getHost();
        }

        return $new;
    }

    /**
     * Obtener cuerpo del mensaje HTTP
     **/
    private function getContent(): void
    {
        //contenido
        if (
            in_array($this->getMethod(), ['PUT', 'DELETE', 'PATCH'])
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
     **/
    public function isFormData(): bool
    {
        $content_type = explode(';', $this->getHeaderLine('content-type'))[0];
        return ($this->hasMethod('POST') && in_array($content_type, $this->form_content_type));
    }

    /**
     * Obtener parámetros
     *
     * En caso de no especificar el parametro a devolver este metodo devuelve 
     * todos los valores del $params propiedad. Puede agregarle valores por defecto en caso de 
     * que $params[$name] no este definido.
     **/
    private function params(array $params, ?string $name = null, mixed $default = null): mixed
    {
        if (empty($name)) {
            return $params;
        }

        if (isset($params[$name])) {
            return $params[$name];
        }

        return $default;
    }

    /**
     * Obtener parámetros en la cadena de consulta de la URI
     *
     * En caso de no especificar el parametro a devolver este metodo devuelve 
     * todos los valores de la superglobal $_GET. Puede agregarle valores a $_GET especificando 
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
        if (!is_null($this->content)) {
            parse_str($this->content, $output);
            return $this->params($output, $name, $default);
        }

        //Si no hay contenido, devolvemos null o el valor por defecto
        return $default;
    }

    /**
     * Devuelve JSON decodificado
     **/
    public function jsonData(bool $assoc = true): array|object|null
    {
        if ($this->getHeaderLine('content-type') === 'application/json') {
            return json_decode($this->content, $assoc, flags: JSON_THROW_ON_ERROR);
        }
        return null;
    }

    /**
     * Devuelve el cuerpo de la solicitud sin tratar
     **/
    public function rawData(): ?string
    {
        return $this->content;
    }

    /**
     * Obtiene ficheros subidos al servidor
     */
    public function files(): array
    {
        return $this->files ??  [];
    }

    /**
     * Crea una instancia del objeto UploadedFile
     */
    private static function createUploadedFile(array $value): UploadedFile
    {
        return new UploadedFile(
            $value["tmp_name"],
            $value["error"],
            $value["size"],
            $value["name"] ?? null,
            $value["type"] ?? null
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
                    $this->files[$key][] = self::createUploadedFile(
                        [
                            'name'     => $file['name'][$i]      ?? null,
                            'type'     => $file['type'][$i]      ?? null,
                            'tmp_name' => $file['tmp_name'][$i]  ?? null,
                            'error'    => $file['error'][$i]     ?? null,
                            'size'     => $file['size'][$i]      ?? null,
                        ]
                    );
                }
            } else {
                $this->files[$key] = self::createUploadedFile($file);
            }
        }
    }
}
