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
}
