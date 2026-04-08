<?php

namespace Mk4U\Http;

/**
 * Headers Trait
 */
trait Headers
{
    /** @param string version del protocolo http*/
    protected string $version = 'HTTP/1.1';

    /** @param array version del protocolo http*/
    protected const VERSIONS = ['1.0', '1.1', '2.0', '3.0'];

    /** @param array headers del mensaje http*/
    protected array $headers = [];

    /**
     * Version del Protocolo Http
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * Version del Protocolo Http
     */
    public function setProtocolVersion(?string $version = null): static
    {
        // Si la versión es nula, no se establece nada y se retorna la instancia actual
        if ($version === null) {
            return $this; // No se establece nada si la versión es nula 
        }

        // Validar que la versión sea una de las permitidas
        if (!in_array($version, self::VERSIONS, true)) {
            throw new \InvalidArgumentException("Invalid HTTP protocol version: $version");
        }

        // Asignar la versión
        $this->version = "HTTP/$version";

        return $this; // Retornar la instancia actual
    }

    /**
     * Obtener todas las Cabeceras
     */
    public function getHeaders(): array
    {
        return $this->headers;
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
     * Verificar si existe una cabecera
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$this->sanitizeHeader($name)]);
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
     * Establecer cabecera
     */
    public function setHeader(string $name, string|array $value): static
    {
        $this->headers[$this->sanitizeHeader($name)] = $this->sanitizeHeaderValue($value);

        return $this;
    }

    /**
     * Establecer todas las cabeceras
     */
    public function setHeaders(array $headers): static
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Agregar cabecera
     * 
     * Si existe se agrega el valor al final
     */
    public function addHeader(string $name, string|array $value): static
    {
        $key = $this->sanitizeHeader($name);

        if (!$this->hasHeader($name)) {
            return $this->setHeader($name, $value);
        }

        $current = $this->headers[$key];
        if (is_string($current)) {
            $current = [$current];
        } elseif (!is_array($current)) {
            $current = [];
        }

        if (is_array($value)) {
            $current = array_merge($current, $value);
        } else {
            $current[] = $value;
        }

        $this->headers[$key] = $this->sanitizeHeaderValue($current);
        return $this;
    }

    /**
     * Eliminar Cabecera
     */
    public function removeHeader(string $name): static
    {
        if ($this->hasHeader($name)) unset($this->headers[$this->sanitizeHeader($name)]);
        return $this;
    }

    /**
     * Estandarizar nombre de cabecera
     */
    private function sanitizeHeader(string $name): string
    {
        return str_replace(' ', '-', ucwords(str_replace(['-', '_'], ' ', strtolower($name))));
    }

    /**
     * Sanitiza el valor de una cabecera para prevenir CRLF injection.
     * 
     * Elimina caracteres de control \r y \n que podrían ser usados para
     * inyectar cabeceras adicionales en respuestas HTTP (HTTP Response Splitting).
     *
     * @param string|array $value Valor de la cabecera a sanitizar
     * @return string|array Valor sanitizado
     */
    private function sanitizeHeaderValue(string|array $value): string|array
    {
        if (is_array($value)) {
            return array_map(fn($v) => is_string($v) ? preg_replace('/[\r\n]/', '', $v) : $v, $value);
        }
        return preg_replace('/[\r\n]/', '', $value);
    }
}
