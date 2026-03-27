<?php

namespace Mk4U\Http;

use Psr\Http\Message\StreamInterface;

/**
 * Describe un flujo de datos.
 *
 * Normalmente, una instancia envolverá una secuencia PHP; esta interfaz proporciona un 
 * resumen de las operaciones más comunes, incluida la serialización de toda la 
 * secuencia a una cadena.
 *
 * Implementa PSR-7 StreamInterface
 * @see https://www.php-fig.org/psr/psr-7/#36-psrhttpmessagestreaminterface
 */
class Stream implements StreamInterface
{
    // Array de modos leíbles
    private const readableModes = [
        'r',    // Lectura
        'rb',   // Lectura en modo binario
        'rt',   // Lectura en modo texto
        'r+',   // Lectura y escritura
        'rb+',  // Lectura y escritura en modo binario
        'rt+',  // Lectura y escritura en modo texto
        'a+',   // Escritura (agregar) y lectura
        'ab+',  // Escritura (agregar) y lectura en modo binario
        'w+',   // Escritura y lectura
        'wb+',  // Escritura y lectura en modo binario
        'x+',   // Creación y escritura (fallará si el archivo ya existe)
        'xb+',  // Creación y escritura en modo binario (fallará si el archivo ya existe)
        'c+',   // Escritura (truncar) y lectura
        'cb+',  // Escritura (truncar) y lectura en modo binario
        'w+b',  // Escritura y lectura binario (php://memory)
    ];

    // Array de modos escribibles
    private const writableModes = [
        'w',    // Escritura (truncar)
        'wb',   // Escritura en modo binario (truncar)
        'wt',   // Escritura en modo texto (truncar)
        'a',    // Escritura (agregar)
        'ab',   // Escritura (agregar) en modo binario
        'at',   // Escritura (agregar) en modo texto
        'c',    // Escritura (truncar)
        'x',    // Creación y escritura (fallará si el archivo ya existe)
        'r+',   // Lectura y escritura
        'rb+',  // Lectura y escritura en modo binario
        'rw',   // Lectura y escritura (no es un modo estándar en PHP, pero se incluye aquí para referencia)
        'c+',   // Escritura (truncar)
        'w+',   // Escritura y lectura
        'w+b',  // Escritura y lectura binario (php://memory)
        'a+',   // Escritura y lectura
    ];


    public function __construct(private mixed $stream, string $mode = 'r')
    {
        if ($stream === null) {
            $this->stream = null;
            return;
        }

        if (is_resource($stream)) {
            $this->stream = $stream;
            return;
        }

        $this->stream = @fopen($stream, $mode);

        if ($this->stream === false) {
            throw new \RuntimeException("Could not open the resource: $stream with mode: $mode");
        }
    }

    /**
     * Lee todos los datos de la secuencia en una cadena, desde el principio hasta el final.
     *
     * Advertencia: Esto podría intentar cargar una gran cantidad de datos en la memoria.
     *
     * Este método NO DEBE generar una excepción para cumplir con PHP operaciones 
     * de fundición de cuerdas.
     */
    public function __toString(): string
    {
        if ($this->stream !== null) {
            $this->seek(0);
            return $this->getContents();
        }

        return '';
    }

    /**
     * Cierra la transmisión y cualquier recurso subyacente.
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * Separa los recursos subyacentes del stream.
     * 
     * @return resource|null Stream subyacente de PHP, o null si no hay.
     */
    public function detach(): mixed
    {
        $stream = $this->stream;
        $this->stream = null;
        return $stream;
    }

    /**
     * Obtiene el tamaño del stream si se conoce.
     * 
     * @return int|null Tamaño en bytes si se conoce, o null si es desconocido.
     */
    public function getSize(): ?int
    {
        if ($this->stream === null) {
            return null;
        }
        $stats = fstat($this->stream);
        return $stats['size'] ?? null;
    }

    /**
     * Devuelve la posición actual del puntero de lectura/escritura del archivo.
     * 
     * @return int Posición del puntero del archivo
     * @throws \RuntimeException Si ocurre un error.
     */
    public function tell(): int
    {
        if ($this->stream === null) {
            throw new \RuntimeException('Stream is detached');
        }
        $position = ftell($this->stream);
        if ($position === false) {
            throw new \RuntimeException('Error getting position');
        }
        return $position;
    }

    /**
     * Devuelve verdadero si el puntero está al final del stream.
     * 
     * @return bool
     */
    public function eof(): bool
    {
        if ($this->stream === null) {
            return true;
        }
        return feof($this->stream);
    }

    /**
     * Devuelve si la transmisión es buscable o no.
     */
    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable') ?? false;
    }

    /**
     * Busca una posición en el stream.
     *
     * @link http://www.php.net/manual/es/function.fseek.php
     * @param int $offset Desplazamiento del stream
     * @param int $whence Especifica cómo se calculará la posición del cursor
     * @throws \RuntimeException Si falla.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if ($this->stream === null) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }
        $result = fseek($this->stream, $offset, $whence);
        if ($result === -1) {
            throw new \RuntimeException('Error seeking in stream');
        }
    }

    /**
     * Rebobina el stream hasta el inicio.
     *
     * Si el stream no es buscable, este método lanzará una excepción;
     * de lo contrario, realizará un seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/es/function.rewind.php
     * @throws \RuntimeException Si falla.
     */
    public function rewind(): void
    {
        if ($this->stream === null) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }
        if (!rewind($this->stream)) {
            throw new \RuntimeException('Error rewinding stream');
        }
    }

    /**
     * Devuelve si se puede escribir en la secuencia o no.
     */
    public function isWritable(): bool
    {
        return in_array($this->getMetadata('mode'), self::writableModes);
    }

    /**
     * Escribe datos en el stream.
     *
     * @param string $data La cadena que se va a escribir.
     * @return int Número de bytes escritos en el stream.
     * @throws \RuntimeException Si falla.
     */
    public function write(string $data): int
    {
        if ($this->stream === null) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }
        $result = fwrite($this->stream, $data);
        if ($result === false) {
            throw new \RuntimeException('Error writing to stream');
        }
        return $result;
    }

    /**
     * Devuelve si la transmisión es legible o no.
     */
    public function isReadable(): bool
    {
        return in_array($this->getMetadata('mode'), self::readableModes);
    }

    /**
     * Lee datos del stream.
     *
     * @param int $length Lee hasta $length bytes del objeto y los retorna.
     * Se pueden devolver menos de $length bytes si el stream subyacente
     * devuelve menos bytes.
     * @return string Datos leídos del stream, o cadena vacía si no hay bytes disponibles.
     * @throws \RuntimeException Si ocurre un error.
     */
    public function read(int $length): string
    {
        if ($this->stream === null) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }
        $result = fread($this->stream, $length);
        if ($result === false) {
            throw new \RuntimeException('Error reading from stream');
        }
        return $result;
    }

    /**
     * Devuelve el contenido restante del stream.
     *
     * @return string Contenido del stream.
     * @throws \RuntimeException Si no se puede leer o ocurre un error.
     */
    public function getContents(): string
    {
        if ($this->stream === null) {
            throw new \RuntimeException('Stream is detached');
        }
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }
        $result = stream_get_contents($this->stream);
        if ($result === false) {
            throw new \RuntimeException('Error reading from stream');
        }
        return $result;
    }

    /**
     * Obtiene metadatos del stream como array asociativo o recupera una clave específica.
     *
     * Las claves devueltas son idénticas a las devueltas por la función
     * stream_get_meta_data() de PHP.
     *
     * @see http://php.net/manual/es/function.stream-get-meta-data.php
     * @param string|null $key Metadatos específicos a recuperar.
     * @return array|mixed|null Array asociativo si no se proporciona clave.
     * Valor específico si se proporciona y se encuentra, o null si no se encuentra.
     */
    public function getMetadata(?string $key = null): mixed
    {
        if ($this->stream === null) {
            return null;
        }
        $meta = stream_get_meta_data($this->stream);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    /**
     * Cerrar flujo al destruir la clase
     */
    public function __destruct()
    {
        $this->close();
    }
}
