<?php

namespace Mk4U\Http;

/**
 * Client class
 */
class Client
{
    private \CurlHandle $curl;
    private Request $request;
    private const METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'PATCH'
    ];

    /**
     * Inicializa Curl
     */
    public function __construct(private array $optionDefault = [])
    {
        // Verifica si la extensión cURL está cargada
        if (!\extension_loaded('curl')) {
            throw new \RuntimeException('The "cURL" extension is not installed.');
        }

        $this->optionDefault = $optionDefault;
        $this->curl = \curl_init(); // Inicializa cURL
    }

    /**
     * Cierra la sesión de cURL
     */
    public function __destruct()
    {
        // Cierra la sesión de cURL
        unset($this->curl);
    }

    /**
     * Envía la petición
     */
    public function request(string $method, string $uri, array $options = []): Response
    {
        //Obtener cabeceras
        $headers = $options['headers'] ?? [];

        // Obtiene la petición
        $this->request = new Request(
            $method,
            $uri,
            array_merge($this->optionDefault['headers'] ?? [], $headers)
        );

        // Verifica el método
        if (!in_array($this->request->getMethod(), self::METHODS)) {
            throw new \InvalidArgumentException("Http method '$method' not implemented.");
        }

        // Establece las opciones
        $this->setOptions(array_merge($this->optionDefault, $options));

        // Ejecuta cURL y retorna la respuesta
        return $this->response(\curl_exec($this->curl));
    }

    /**
     * Recibe la respuesta
     */
    private function response(string|bool $response): Response
    {
        if (!$response) {
            throw new \Error(
                sprintf(
                    'cURL error #%d: %s [%s]',
                    \curl_errno($this->curl),
                    \curl_error($this->curl),
                    'see https://curl.haxx.se/libcurl/c/libcurl-errors.html'
                )
            );
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
            $response,
            Status::tryFrom($statusCode),
            $this->request->getHeaders(),
            $version
        );
    }

    /**
     * Ejecuta peticiones para cada método específico
     */
    public function __call($method, $arguments): Response
    {
        if (empty($arguments)) {
            throw new \InvalidArgumentException("Error processing empty arguments.");
        }

        return $this->request($method, $arguments[0], $arguments[1] ?? []);
    }

    /**
     * Establece las opciones de configuración de cURL
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
            \CURLOPT_FOLLOWLOCATION => true, // Permite seguir redirecciones
            \CURLOPT_VERBOSE        => $options['verbose'] ?? false // Activa la salida detallada para depuración
        ];

        // Manejo del cuerpo de la solicitud
        if (
            $this->request->hasMethod('post') ||
            $this->request->hasMethod('put') ||
            $this->request->hasMethod('patch')
        ) {
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
        if ($this->request->hasMethod('head') || $this->request->hasMethod('options')) {
            $curlopts[\CURLOPT_NOBODY] = true;
        }

        // Establecer URI
        $this->url($options['query'] ?? []);

        // Formatear headers
        $formattedHeaders = [];
        
        foreach ($this->request->getHeaders() as $key => $value) {
            $formattedHeaders[] = $key . ': ' . $value;
            }
            
        // Cabeceras HTTP
        $curlOptions[\CURLOPT_HTTPHEADER] = $formattedHeaders;

        // Certificado SSL
        if (isset($options['verify'])) {
            $this->ssl($options['verify']);
        }

        // Enviar certificado SSL
        if (isset($options['cert'])) {
            $this->cert($options['cert']);
        }

        // Obtener cabecera HTTP para la respuesta
        \curl_setopt(
            $this->curl,
            \CURLOPT_HEADERFUNCTION,
            function ($curl, $header) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) return $len;

                $headers[trim($header[0])] = trim($header[1]);
                $this->request->setHeaders($headers);
                return $len;
            }
        );

        // Establecer las opciones de cURL
        if (\curl_setopt_array($this->curl, $curlOptions) === false) {
            throw new \RuntimeException("Failed to set cURL options.");
        }
    }

    /**
     * Establece las opciones de URL
     */
    private function url(array $query): void
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
     * Establece las opciones de SSL
     */
    private function ssl(bool $verify): void
    {
        if ($verify) {
            \curl_setopt_array($this->curl, [
                \CURLOPT_SSL_VERIFYHOST => 2, // Verifica nombre del host y del certificado
                \CURLOPT_SSL_VERIFYPEER => true, // Verifica el certificado SSL
            ]);
        } else {
            \curl_setopt_array($this->curl, [
                \CURLOPT_SSL_VERIFYHOST => 0, // No verifica el nombre del host
                \CURLOPT_SSL_VERIFYPEER => false, // No verifica el certificado SSL
            ]);
        }
    }

    /**
     * Establece el certificado SSL a enviar
     */
    private function cert(string $filename): void
    {
        if (!is_null($filename)) {
            if (is_file($filename) && file_exists($filename)) {
                curl_setopt($this->curl, \CURLOPT_CAINFO, $filename); // Ruta al archivo CA
            } elseif (is_dir($filename) && file_exists($filename)) {
                curl_setopt($this->curl, \CURLOPT_CAPATH, $filename); // Ruta al directorio de CA
            } else {
                throw new \InvalidArgumentException("Invalid certificate in $filename");
            }
        }
    }

    /**
     * Normaliza un valor multipart para que cURL lo entienda
     *
     * Acepta:
     * - CURLFile
     * - Stream (Mk4U\Http\Stream)
     * - resource (fopen)
     * - string (texto o path)
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
                throw new \RuntimeException(
                    'Stream/resource must be backed by a file'
                );
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

        throw new \InvalidArgumentException(
            'Unsupported multipart contents type'
        );
    }
}
