<?php

namespace Mk4U\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Response class
 * 
 * 
 * 
 * Representación de una respuesta saliente del lado del servidor.
 * 
 * Según la especificación HTTP, esta interfaz incluye propiedades para cada uno de los siguientes:
 * 
 * - Versión del protocolo
 * - Código de estado y frase de motivo
 * - Encabezados
 * - Cuerpo del mensaje
 * 
 * Las respuestas se consideran inmutables; todos los métodos que puedan cambiar de estado DEBEN implementarse 
 * de manera que conserven el estado interno del mensaje actual y devuelvan una instancia que contenga 
 * el estado cambiado.
 */
class Response extends Message implements ResponseInterface
{
    /** @param int código de estado HTTP*/
    protected int $code;

    /** @param string frase de motivo de respuesta asociada con el código de estado*/
    protected string $phrase;

    public function __construct(mixed $content = "", Status|array $status = Status::Ok, array $headers = [], ?string $version = null)
    {

        //version protocolo
        $this->version = !is_null($version) ? "HTTP/$version" : 'HTTP/1.1';


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
        $this->headers = $headers;

        //establecer cuerpo del mensaje
        $this->body = new Stream('php://temp', 'r+');
        $this->body->write((string) $content);
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
     * Devuelve una instancia con el código de estado especificado y, opcionalmente, la frase de motivo.
     * 
     * Si no se especifica ninguna frase de motivo, las implementaciones PUEDEN optar por el valor predeterminado
     * a la frase de motivo recomendada por RFC 7231 o IANA para la respuesta
     * código de estado.
     * 
     * Este método DEBE implementarse de tal manera que conserve la
     * inmutabilidad del mensaje, y DEBE devolver una instancia que tenga la
     * Estado actualizado y frase de motivo.
     * 
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code El código de resultado entero de 3 dígitos que se establecerá.
     * @param string $reasonPhrase La frase de motivo a usar con el
     * código de estado proporcionado; si no se proporciona ninguno, las implementaciones PUEDEN
     * utilice los valores predeterminados como se sugiere en la especificación HTTP.
     * @return static
     * @throws \InvalidArgumentException Para argumentos de código de estado no válidos.
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;

        if ($code < 100 || $code > 599) {
            throw new \InvalidArgumentException("Invalid status code arguments");
        }

        $new->code = $code;
        $new->phrase = empty($reasonPhrase) ? Status::phrase($code) : $reasonPhrase;

        return $new;
    }

    /**
     * Obtiene la frase del motivo de la respuesta asociada al código de estado.
     * 
     * Porque una frase de motivo no es un elemento obligatorio en una respuesta
     * línea de estado, el valor de la frase de motivo PUEDE estar vacío. Implementaciones MAYO
     * elija devolver la frase de motivo recomendada por RFC 7231 predeterminada (o aquellas
     * incluido en el Registro de códigos de estado HTTP de IANA) para la respuesta
     * código de estado.
     * 
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Frase de motivo; debe devolver una cadena vacía si no hay ninguna presente.
     */
    public function getReasonPhrase(): string
    {
        return $this->phrase;
    }

    /**
     * Envia el mensaje HTTP
     */
    protected function send(): string
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
