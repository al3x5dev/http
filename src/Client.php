<?php

namespace Mk4U\Http;

use Mk4U\Http\Exceptions\ClientException;
use Mk4U\Http\Exceptions\ConnectException;
use Mk4U\Http\Exceptions\TimeoutException;

/**
 * HTTP client
 * 
 * Cliente HTTP con API similar a Guzzle para consumir APIs y servicios web.
 * Soporta JSON, form-data, multipart, autenticación, proxy, SSL y más.
 */
class Client
{
    /** @var \CurlHandle Instancia de cURL */
    private \CurlHandle $curl;

    /** @var Request Request actual */
    private Request $request;

    /** @var string URI base para requests relativos */
    private string $baseUri = '';

    /** @var resource|null File handle para sink (descarga a archivo) */
    private $sinkHandle = null;

    /** @var array Métodos HTTP soportados */
    private const METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'PATCH'
    ];

    /** @var array Esquemas de URI permitidos para prevenir SSRF */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * Inicializa Curl
     * 
     * @param array $config Configuración del cliente
     */
    public function __construct(private array $config = [])
    {
        if (!\extension_loaded('curl')) {
            throw new \RuntimeException('The "cURL" extension is not installed.');
        }

        $this->baseUri = $config['base_uri'] ?? '';
        $this->curl = \curl_init();
    }

    /**
     * Cierra la sesión de cURL y libera recursos
     */
    public function __destruct()
    {
        // Cierra el file handle del sink si está abierto
        if (is_resource($this->sinkHandle)) {
            \fclose($this->sinkHandle);
        }

        // Cierra la sesión de cURL
        if (isset($this->curl)) {
            \curl_close($this->curl);
        }
    }

    /**
     * Envía la petición HTTP
     * 
     * @param string $method Método HTTP
     * @param string $uri URI de la petición
     * @param array $options Opciones de la petición
     * @return Response
     */
    public function request(string $method, string $uri, array $options = []): Response
    {
        $uri = $this->resolveUri($uri);

        // Obtener cabeceras
        $headers = $options['headers'] ?? [];

        // Obtiene la petición
        $this->request = new Request(
            $method,
            $uri,
            array_merge($this->config['headers'] ?? [], $headers)
        );

        // Verifica el método
        if (!in_array($this->request->getMethod(), self::METHODS)) {
            throw new \InvalidArgumentException("Http method '$method' not implemented.");
        }

        // Establece las opciones
        $this->setOptions(array_merge($this->config, $options));

        // Ejecuta cURL y retorna la respuesta
        $result = \curl_exec($this->curl);
        return $this->handleResponse($result);
    }

    /**
     * Maneja la respuesta o error
     * 
     * @param string|bool $result Resultado de curl_exec
     * @return Response
     */
    private function handleResponse(string|bool $result): Response
    {
        if ($result === false) {
            $errno = \curl_errno($this->curl);
            $error = \curl_error($this->curl);
            $uri = (string) $this->request->getUri();
            $method = $this->request->getMethod();

            throw $this->createException($errno, $error, $uri, $method);
        }

        // Obtiene el código de estado HTTP
        $statusCode = \curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        // Obtiene versión del protocolo
        $version = match (\curl_getinfo($this->curl, CURLINFO_HTTP_VERSION)) {
            \CURL_HTTP_VERSION_1_0 => '1.0',
            \CURL_HTTP_VERSION_1_1 => '1.1',
            \CURL_HTTP_VERSION_2_0 => '2',
            default => null,
        };

        // Devuelve un nuevo objeto Response
        return new Response(
            $result,
            Status::tryFrom($statusCode ?? 500),
            $this->request->getHeaders(),
            $version
        );
    }

    /**
     * Crea la excepción según el tipo de error cURL
     * 
     * @param int $errno Código de error de cURL
     * @param string $error Mensaje de error de cURL
     * @param string $uri URI de la petición
     * @param string $method Método HTTP
     * @return \Exception
     */
    private function createException(int $errno, string $error, string $uri, string $method): \Exception
    {
        // CURLE_OPERATION_TIMEDOUT = 28 (constante estándar de cURL)
        // Se usa la constante si existe, sino fallback numérico
        $timeoutCode = defined('CURLE_OPERATION_TIMEDOUT') ? CURLE_OPERATION_TIMEDOUT : 28;

        return match ($errno) {
            CURLE_COULDNT_CONNECT,
            CURLE_COULDNT_RESOLVE_HOST,
            CURLE_COULDNT_RESOLVE_PROXY => new ConnectException(
                "Connection failed: $error",
                null,
                $uri,
                $method
            ),
            $timeoutCode => new TimeoutException(
                "Request timed out: $error",
                null,
                $uri,
                $method
            ),
            default => new ClientException(
                "cURL error #$errno: $error",
                0,
                null,
                $uri,
                $method
            ),
        };
    }

    /**
     * Resuelve la URI relativa contra base_uri
     * 
     * Valida esquemas permitidos para prevenir SSRF.
     * 
     * @param string $uri URI a resolver
     * @return string URI resuelta
     */
    private function resolveUri(string $uri): string
    {
        // Si no hay base_uri, retorna la URI tal cual
        if ($this->baseUri === '') {
            return $uri;
        }

        // Si la URI es absoluta, la retorna tal cual
        if (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://')) {
            return $uri;
        }

        // Combina base_uri con URI relativa
        $base = rtrim($this->baseUri, '/');
        $uri = ltrim($uri, '/');

        return "$base/$uri";
    }

    /**
     * Ejecuta peticiones para cada método específico
     * 
     * @param string $method Nombre del método HTTP
     * @param array $arguments [uri, options]
     * @return Response
     */
    public function __call(string $method, array $arguments): Response
    {
        if (empty($arguments)) {
            throw new \InvalidArgumentException("Error processing empty arguments.");
        }

        if (!in_array($method, self::METHODS)) {
            throw new \RuntimeException("Unsupported HTTP methods");
        }
        return $this->request($method, $arguments[0], $arguments[1] ?? []);
    }

    /**
     * Establece las opciones de configuración de cURL
     * 
     * @param array $options Opciones combinadas de config y request
     */
    private function setOptions(array $options): void
    {
        $curlOptions = [
            // Opciones por defecto
            \CURLOPT_RETURNTRANSFER => true, // Devuelve la respuesta en lugar de imprimir
            \CURLOPT_MAXREDIRS      => $options['max_redirects'] ?? 10, // Limita las redirecciones a 10
            \CURLOPT_TIMEOUT        => $options['timeout'] ?? 30, // Tiempo máximo para recibir respuesta
            \CURLOPT_CONNECTTIMEOUT => $options['connect_timeout'] ?? 30, // Tiempo máximo para conectar
            \CURLOPT_HTTP_VERSION   => $options['http_version'] ?? \CURL_HTTP_VERSION_1_1, // Versión HTTP
            \CURLOPT_USERAGENT      => $options['user_agent'] ?? 'Mk4U/HTTP Client', // Define el User-Agent
            \CURLOPT_ENCODING       => $options['encoding'] ?? '', // Maneja las codificaciones
            \CURLOPT_AUTOREFERER    => $options['auto_referer'] ?? true, // Establece Referer en redirecciones
            \CURLOPT_CUSTOMREQUEST  => $this->request->getMethod(), // Método HTTP a usar
            \CURLOPT_FOLLOWLOCATION => $options['allow_redirects'] ?? true, // Permite seguir redirecciones
            \CURLOPT_VERBOSE        => $options['debug'] ?? false // Activa la salida detallada para depuración
        ];

        // Maneja el cuerpo de la petición (pasa $curlOptions por referencia)
        $this->handleBodyOptions($options, $curlOptions);

        if ($this->request->hasMethod('head') || $this->request->hasMethod('options')) {
            $curlOptions[\CURLOPT_NOBODY] = true;
        }

        $this->handleQuery($options['query'] ?? []);
        $this->handleHeaders();
        $this->handleAuth($options['auth'] ?? null);
        $this->handleProxy($options['proxy'] ?? null);
        $this->handleSink($options['sink'] ?? null);
        $this->handleSsl($options['verify'] ?? true);
        $this->handleCert($options['cert'] ?? null);

        // Establecer las opciones de cURL
        if (\curl_setopt_array($this->curl, $curlOptions) === false) {
            throw new \RuntimeException("Failed to set cURL options.");
        }
    }

    /**
     * Maneja opciones de cuerpo (json, form_params, multipart, body)
     * 
     * @param array $options Opciones de la petición
     * @param array $curlOptions Opciones de cURL (se modifica por referencia)
     */
    private function handleBodyOptions(array $options, array &$curlOptions): void
    {
        if (
            !$this->request->hasMethod('post') &&
            !$this->request->hasMethod('put') &&
            !$this->request->hasMethod('patch')
        ) {
            return;
        }

        if (isset($options['form_params'])) {
            // application/x-www-form-urlencoded
            $curlOptions[\CURLOPT_POSTFIELDS] = http_build_query($options['form_params']);
            $this->request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        } elseif (isset($options['json'])) {
            // application/json
            $curlOptions[\CURLOPT_POSTFIELDS] = json_encode($options['json']);
            $this->request->setHeader('Content-Type', 'application/json');
        } elseif (isset($options['multipart'])) {
            // multipart/form-data
            $multipart = [];

            foreach ($options['multipart'] as $part) {

                if (!isset($part['name'], $part['contents'])) {
                    throw new \InvalidArgumentException(
                        'Each multipart entry must have name and contents'
                    );
                }

                $multipart[$part['name']] = $this->normalizeMultipartValue(
                    $part['contents'],
                    $part['filename'] ?? null,
                    $part['headers']['Content-Type'] ?? null
                );
            }

            $curlOptions[\CURLOPT_POSTFIELDS] = $multipart;
        } elseif (isset($options['body'])) {
            // text/plain
            $curlOptions[\CURLOPT_POSTFIELDS] = $options['body'];
            $this->request->setHeader('Content-Type', 'text/plain');
        }
    }

    /**
     * Maneja query parameters
     * 
     * @param array $query Query parameters
     */
    private function handleQuery(array $query): void
    {
        if (!empty($query)) {
            // Agrega las query
            $uri = $this->request->getUri()->setQuery(http_build_query($query));
            \curl_setopt($this->curl, CURLOPT_URL, $uri);
        } else {
            // Sin query
            \curl_setopt($this->curl, CURLOPT_URL, $this->request->getUri());
        }
    }

    /**
     * Formatea y establece headers
     */
    private function handleHeaders(): void
    {
        $formattedHeaders = [];
        foreach ($this->request->getHeaders() as $key => $value) {
            if (is_array($value)) {
                $formattedHeaders[] = "$key: {$this->request->getHeaderLine($key)}";
            } else {
                $formattedHeaders[] = "$key: $value";
            }
        }
        \curl_setopt($this->curl, \CURLOPT_HTTPHEADER, $formattedHeaders);
    }

    /**
     * Maneja autenticación básica
     * 
     * @param array|null $auth [username, password]
     */
    private function handleAuth(?array $auth): void
    {
        if ($auth === null) {
            return;
        }

        $username = $auth[0] ?? '';
        $password = $auth[1] ?? '';
        $encoded = base64_encode("$username:$password");

        $this->request->setHeader('Authorization', "Basic $encoded");
    }

    /**
     * Maneja proxy HTTP
     * 
     * @param string|null $proxy URL del proxy
     */
    private function handleProxy(?string $proxy): void
    {
        if ($proxy !== null) {
            \curl_setopt($this->curl, \CURLOPT_PROXY, $proxy);
        }
    }

    /**
     * Maneja sink (descargar a archivo)
     * 
     * Almacena el file handle para cerrarlo en el destructor.
     * 
     * @param string|null $sink Ruta del archivo
     */
    private function handleSink(?string $sink): void
    {
        if ($sink !== null) {
            $fp = fopen($sink, 'w');
            if ($fp === false) {
                throw new \InvalidArgumentException("Cannot open sink file: $sink");
            }
            $this->sinkHandle = $fp;
            \curl_setopt($this->curl, \CURLOPT_FILE, $fp);
        }
    }

    /**
     * Maneja opciones SSL
     * 
     * @param bool $verify Verificar certificado SSL
     */
    private function handleSsl(bool $verify): void
    {
        \curl_setopt_array($this->curl, [
            \CURLOPT_SSL_VERIFYHOST => $verify ? 2 : 0, // Verifica nombre del host y del certificado
            \CURLOPT_SSL_VERIFYPEER => $verify, // Verifica el certificado SSL
        ]);
    }

    /**
     * Maneja certificado SSL
     * 
     * @param string|null $cert Ruta al archivo o directorio de certificados
     */
    private function handleCert(?string $cert): void
    {
        if ($cert !== null) {
            if (is_file($cert) && file_exists($cert)) {
                \curl_setopt($this->curl, \CURLOPT_CAINFO, $cert); // Ruta al archivo CA
            } elseif (is_dir($cert) && file_exists($cert)) {
                \curl_setopt($this->curl, \CURLOPT_CAPATH, $cert); // Ruta al directorio de CA
            } else {
                throw new \InvalidArgumentException("Invalid certificate: $cert");
            }
        }
    }

    /**
     * Normaliza un valor multipart para cURL
     * 
     * Acepta:
     * - CURLFile
     * - Stream (Mk4U\Http\Stream)
     * - resource (fopen)
     * - string (texto o path)
     * 
     * @param mixed $value Valor a normalizar
     * @param string|null $filename Nombre del archivo
     * @param string|null $contentType Content-Type
     * @return mixed
     */
    private function normalizeMultipartValue(
        mixed $value,
        ?string $filename = null,
        ?string $contentType = null
    ): mixed {
        // Si es una instancia de CURLFile lo usa directamente
        if ($value instanceof \CURLFile) {
            return $value;
        }

        // Stream o resource
        if ($value instanceof Stream || is_resource($value)) {

            // Si es tu Stream, lo convertimos a resource
            if ($value instanceof Stream) {
                $value = $value->detach();
            }

            if (!is_resource($value)) {
                throw new \RuntimeException('Invalid stream resource');
            }

            $meta = stream_get_meta_data($value);
            $path = $meta['uri'] ?? null;

            if (!$path || !is_file($path)) {
                throw new \RuntimeException('Stream/resource must be backed by a file');
            }

            return new \CURLFile(
                $path,
                $contentType,
                $filename ?? basename($path)
            );
        }

        // Texto plano
        if (is_scalar($value)) {
            // Si es path válido → archivo
            if (is_string($value) && is_file($value)) {
                return new \CURLFile(
                    $value,
                    $contentType,
                    $filename ?? basename($value)
                );
            }
            // Si no, se envía como campo normal
            return (string) $value;
        }

        throw new \InvalidArgumentException('Unsupported multipart contents type');
    }

    /**
     * Obtiene configuración del cliente
     * 
     * @param string|null $key Clave específica o null para toda la config
     * @return mixed
     */
    public function getConfig(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }
}
