<?php

namespace Mk4U\Http;

/**
 * Response class
 * 
 * Representación de una respuesta saliente del lado del servidor.
 * 
 * Incluye propiedades para:
 * - Versión del protocolo
 * - Código de estado y frase de motivo
 * - Encabezados
 * - Cuerpo del mensaje
 */
class Response
{
    /** @param int código de estado HTTP*/
    protected int $code;

    /** @param string frase de motivo de respuesta asociada con el código de estado*/
    protected string $phrase;

    /** @param mixed cuerpo del mensaje http*/
    protected mixed $body;

    use Headers;

    public function __construct(
        mixed $content = "",
        Status|array $status = Status::Ok,
        array $headers = [],
        ?string $version = null
    ) {

        //version protocolo
        $this->setProtocolVersion($version);

        if (is_array($status)) {
            if (!isset($status[0]) || !isset($status[1])) {
                throw new \InvalidArgumentException('Status array must contain [code, phrase]');
            }
            $this->code = $status[0];
            $this->phrase = $status[1];
        } else {
            $this->code = $status->value;
            $this->phrase = Status::phrase($this->code);
        }

        //establecer cabeceras
        $this->setHeaders($headers);

        //establecer cuerpo del mensaje
        $this->setBody($content);
    }

    public function __toString(): string
    {
        return $this->send();
    }

    /**
     * Debuguear mensanje de la respuesta HTTP
     */
    public function __debugInfo(): array
    {
        return [
            "protocol" => $this->getProtocolVersion(),
            "code"     => $this->getStatusCode(),
            "phrase"   => $this->getReasonPhrase(),
            "headers"  => $this->getHeaders(),
            "body"     => $this->getBody()
        ];
    }

    /**
     * Obtiene el código de estado de respuesta.
     */
    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * Establece el código de estado y, opcionalmente, la frase de motivo.
     */
    public function setStatus(int $code, string $reasonPhrase = ''): Response
    {
        if ($code < 100 || $code > 599) {
            throw new \InvalidArgumentException("Invalid status code arguments");
        }

        $this->code = $code;
        $this->phrase = empty($reasonPhrase) ? Status::phrase($code) : $reasonPhrase;

        return $this;
    }

    /**
     * Obtiene la frase del motivo de la respuesta asociada al código de estado.
     */
    public function getReasonPhrase(): string
    {
        return $this->phrase;
    }

    /**
     * Devuelve cuerpo del mensaje
     */
    public function getBody(): mixed
    {
        return $this->body;
    }

    /**
     * Establece cuerpo del mensaje
     */
    public function setBody(mixed $body): Response
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Envia el mensaje HTTP
     */
    public function send(): string
    {
        header($this->getProtocolVersion() . ' ' . $this->getStatusCode() . ' ' . $this->getReasonPhrase());

        foreach ($this->getHeaders() as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header("$name: $v", false);
                }
            } else {
                header("$name: $value");
            }
        }

        return (string) $this->getBody();
    }

    /**
     * Devuelve cuerpo del mensaje como JSON
     */
    public static function json(array|string $content, Status|array $status = Status::Ok, array $headers = [], ?string $version = null): Response
    {
        $headers['content-type'] = 'application/json';
        return new static(
            is_string($content) ? $content : json_encode($content, JSON_PRETTY_PRINT),
            $status,
            $headers,
            $version
        );
    }

    /**
     * Devuelve cuerpo del mensaje como texto plano
     */
    public static function plain(string $content, Status|array $status = Status::Ok, array $headers = [], ?string $version = null): Response
    {
        $headers['content-type'] = 'text/plain';
        return new static($content, $status, $headers, $version);
    }

    /**
     * Devuelve cuerpo del mensaje como HTML
     */
    public static function html(string $content, Status|array $status = Status::Ok, array $headers = [], ?string $version = null): Response
    {
        $headers['content-type'] = 'text/html';
        return new static($content, $status, $headers, $version);
    }

    /**
     * Devuelve cuerpo del mensaje como XML
     */
    public static function xml(string $content, Status|array $status = Status::Ok, array $headers = [], ?string $version = null): Response
    {
        $headers['content-type'] = 'application/xml';
        return new static($content, $status, $headers, $version);
    }
    

    /**
     * Descarga un archivo forzando la descarga en el navegador
     * 
     * @param string $filePath Ruta absoluta al archivo
     * @param string|null $filename Nombre personalizado (opcional)
     * @param array $headers Headers adicionales (opcional)
     * @param bool $display Mostrar en navegador en lugar de descargar (opcional)
     * @return Response
     */
    public static function download(
        string $filePath,
        ?string $filename = null,
        array $headers = [],
        bool $display = false
    ): Response {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: $filePath");
        }

        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("File not readable: $filePath");
        }

        $filename = $filename ?? basename($filePath);
        $filesize = filesize($filePath);
        $mimeType = self::guessMimeType($filePath);

        $defaultHeaders = [
            'Content-Type' => $mimeType,
            'Content-Length' => $filesize,
            'Content-Disposition' => $display
                ? "inline; filename=\"$filename\""
                : "attachment; filename=\"$filename\"",
        ];

        $headers = array_merge($defaultHeaders, $headers);

        return new Response(
            file_get_contents($filePath),
            Status::Ok,
            $headers
        );
    }

    /**
     * Muestra un archivo en el navegador (inline)
     * 
     * @param string $filePath Ruta absoluta al archivo
     * @param array $headers Headers adicionales (opcional)
     * @return Response
     */
    public static function file(string $filePath, array $headers = []): Response
    {
        return self::download($filePath, null, $headers, true);
    }

    /**
     * Descarga un archivo desde un stream (para archivos grandes)
     * 
     * @param resource $stream Recurso de stream readable
     * @param string|null $filename Nombre del archivo
     * @param int|null $filesize Tamaño del archivo (opcional)
     * @param array $headers Headers adicionales (opcional)
     * @return Response
     */
    public static function streamDownload(
        $stream,
        ?string $filename = null,
        ?int $filesize = null,
        array $headers = []
    ): Response {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('Invalid stream resource');
        }

        $defaultHeaders = [
            'Content-Type' => 'application/octet-stream',
            'Content-Transfer-Encoding' => 'binary',
        ];

        if ($filename !== null) {
            $defaultHeaders['Content-Disposition'] = "attachment; filename=\"$filename\"";
        }

        if ($filesize !== null) {
            $defaultHeaders['Content-Length'] = $filesize;
        }

        $headers = array_merge($defaultHeaders, $headers);

        return new Response(
            new Stream($stream),
            Status::Ok,
            $headers
        );
    }

    /**
     * Adivina el MIME type basado en la extensión
     */
    private static function guessMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'csv' => 'text/csv',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
