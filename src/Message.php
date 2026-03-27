<?php

namespace Mk4U\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Message class
 * 
 * Clase base que implementa PSR-7 MessageInterface.
 * Proporciona funcionalidad común para Request y Response.
 *
 * @see https://www.php-fig.org/psr/psr-7/#3-psrhttpmessagemessageinterface
 */
class Message implements MessageInterface
{
    /** @var string Versión del protocolo HTTP */
    protected string $version = 'HTTP/1.1';

    /** @var array Versiones de protocolo HTTP válidas */
    protected const VERSIONS = ['1.0', '1.1', '2.0', '3.0'];

    /** @var array Cabeceras del mensaje HTTP */
    protected array $headers = [];

    /** @var StreamInterface Cuerpo del mensaje */
    protected StreamInterface $body;

    /**
     * Constructor de Message
     * 
     * Inicializa el cuerpo del mensaje con un stream vacío en memoria.
     */
    public function __construct()
    {
        $this->body = new Stream('php://memory', 'r+');
    }

    /**
     * Obtiene la versión del protocolo HTTP
     *
     * @return string Versión del protocolo (ej: 'HTTP/1.1')
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * Devuelve una instancia con la versión del protocolo HTTP especificada.
     *
     * Este método DEBE conservarel estado de la instancia actual y devolver
     * una instancia que contenga la versión del protocolo especificada.
     *
     * @param string $version Versión del protocolo (1.0, 1.1, 2.0, 3.0)
     * @return static Nueva instancia con la versión especificada
     * @throws \InvalidArgumentException Para versiones de protocolo no válidas
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        if (!in_array($version, self::VERSIONS, true)) {
            throw new \InvalidArgumentException("Invalid HTTP protocol version: $version");
        }

        $new = clone $this;
        $new->version = "HTTP/$version";
        return $new;
    }

    /**
     * Obtiene todas las cabeceras del mensaje HTTP
     *
     * @return array Array de cabeceras
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Verifica si existe una cabecera específica
     *
     * @param string $name Nombre de la cabecera
     * @return bool True si existe la cabecera
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$this->sanitizeHeader($name)]);
    }

    /**
     * Obtiene una cabecera específica como array
     *
     * @param string $name Nombre de la cabecera
     * @return array Array con los valores de la cabecera
     */
    public function getHeader(string $name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
        }
        
        $value = $this->headers[$this->sanitizeHeader($name)];
        return is_array($value) ? $value : [$value];
    }

    /**
     * Obtiene una cabecera específica como string
     *
     * Los valores son concatenados con coma.
     *
     * @param string $name Nombre de la cabecera
     * @return string Valores de la cabecera concatenados con coma
     */
    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * Devuelve una instancia con la cabecera especificada
     *
     * Este método DEBE conservar el estado de la instancia actual y devolver
     * una instancia que contenga la cabecera especificada.
     *
     * @param string $name Nombre de la cabecera
     * @param mixed $value Valor de la cabecera
     * @return static Nueva instancia con la cabecera especificada
     */
    public function withHeader(string $name, mixed $value): MessageInterface
    {
        $new = clone $this;
        $new->headers[$new->sanitizeHeader($name)] = $value;
        return $new;
    }

    /**
     * Devuelve una instancia con las cabeceras especificada
     *
     * @param string $name Nombre de la cabecera
     * @param mixed $value Valor de la cabecera
     * @return static Nueva instancia con la cabecera especificada
     */
    public function withHeaders(array $headers): MessageInterface
    {
        $new = clone $this;
        foreach ($headers as $name => $value) {
            $new->headers[$new->sanitizeHeader($name)] = $value;
        }
        return $new;
    }

    /**
     * Devuelve una instancia con la cabecera agregada
     *
     * Los valores existentes se concatenan con los nuevos.
     *
     * @param string $name Nombre de la cabecera
     * @param mixed $value Valor a agregar
     * @return static Nueva instancia con la cabecera agregada
     */
    public function withAddedHeader(string $name, mixed $value): MessageInterface
    {
        $new = clone $this;
        $key = $new->sanitizeHeader($name);
        
        if (!$new->hasHeader($name)) {
            $new->headers[$key] = $value;
        } else {
            $current = $new->headers[$key];
            if (is_string($current)) {
                $current = [$current];
            }
            $new->headers[$key] = array_merge((array) $current, (array) $value);
        }
        
        return $new;
    }

    /**
     * Devuelve una instancia sin la cabecera especificada
     *
     * @param string $name Nombre de la cabecera a eliminar
     * @return static Nueva instancia sin la cabecera
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $new = clone $this;
        unset($new->headers[$new->sanitizeHeader($name)]);
        return $new;
    }

    /**
     * Obtiene el cuerpo del mensaje
     *
     * @return StreamInterface Cuerpo del mensaje como stream
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Devuelve una instancia con el cuerpo del mensaje especificado
     *
     * @param StreamInterface $body Cuerpo del mensaje
     * @return static Nueva instancia con el cuerpo especificado
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * Estandariza el nombre de una cabecera
     *
     * Convierte a formato "Title-Case" con guiones.
     *
     * @param string $name Nombre de la cabecera
     * @return string Nombre estandarizado
     */
    private function sanitizeHeader(string $name): string
    {
        return str_replace(' ', '-', ucwords(str_replace(['-', '_'], ' ', strtolower($name))));
    }
}
