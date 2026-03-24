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
        if (!is_resource($stream)) {
            $this->stream = @fopen($stream, $mode);

            if ($this->stream === false) {
                throw new \RuntimeException("Could not open the resource: $stream with mode: $mode");
            }
        } else {
            $this->stream = $stream;
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
        if (isset($this->stream)) {
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
    }

    /**
     * Separa los recursos subyacentes del flujo.
     */
    public function detach(): mixed
    {
        $stream = $this->stream;
        $this->stream = null;
        return $stream;
    }

    /**
     * Obtenga el tamaño de la transmisión si lo conoce.
     */
    public function getSize(): ?int
    {
        return fstat($this->stream)['size'] ?? null;
    }

    /**
     *  Devuelve la posición actual del puntero de lectura/escritura del archivo.
     */
    public function tell(): int
    {
        return ftell($this->stream);
    }

    /**
     * Devuelve verdadero si el puntero está al final de la transmisión.
     */
    public function eof(): bool
    {
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
     *Buscar una posición en la flujo.
     *
     * @see http://www.php.net/manual/es/function.fseek.php
     * 
     * @param int $offset Desplazamiento de flujo
     * @param int $wherece Especifica cómo se calculará la posición del cursor basado 
     * en el desplazamiento de búsqueda.
     * 
     * SEEK_SET: Establecer posición igual a bytes de desplazamiento
     * SEEK_CUR: establece la posición en la ubicación actual más el desplazamiento
     * SEEK_END: establece la posición al final de la transmisión más el desplazamiento.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        fseek($this->stream, $offset, $whence);
    }

    /**
     *Buscar hasta el inicio del arroyo.
     *
     * Si la secuencia no se puede buscar, este método generará una excepción;
     * en caso contrario, realizará una seek(0).
     *
     * @see https://www.php.net/manual/es/function.rewind.php
     */
    public function rewind(): void
    {
        rewind($this->stream);
    }

    /**
     * Devuelve si se puede escribir en la secuencia o no.
     */
    public function isWritable(): bool
    {
        return in_array($this->getMetadata('mode'), self::writableModes);
    }

    /**
     * Escribe datos en la secuencia.
     *
     * @param string $data La cadena que se va a escribir.
     * @return int Devuelve el número de bytes escritos en la secuencia.
     */
    public function write(string $data): int
    {
        return fwrite($this->stream, $data) ?: 0;
    }

    /**
     * Devuelve si la transmisión es legible o no.
     */
    public function isReadable(): bool
    {
        return in_array($this->getMetadata('mode'), self::readableModes);
    }

    /**
     * Leer datos de la transmisión.
     *
     * @param int $length Lee hasta $length bytes del objeto y regresa
     * a ellos. Se pueden devolver menos de $length bytes si la secuencia subyacente
     * la llamada devuelve menos bytes.
     * @return string Devuelve los datos leídos de la secuencia o una cadena vacía si no 
     * hay bytes disponibles.
     */
    public function read(int $length): string
    {
        if ($this->getSize() == 0) {
            return '';
        }
        return fread($this->stream, $length) ?: '';
    }

    /**
     * Devuelve el contenido restante en una cadena
     *
     * @return string
     * @throws \RuntimeException si no se puede leer.
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException("Cannot read the stream. Please ensure that the stream is readable.");
        }

        return stream_get_contents($this->stream);
    }

    /**
     * Obtenga metadatos de transmisión como una matriz asociativa o recupere una clave específica.
     *
     * Las claves devueltas son idénticas a las claves devueltas por PHP
     * Función stream_get_meta_data().
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Metadatos específicos para recuperar.
     * @return array|mixed|null Devuelve una matriz asociativa si no hay ninguna clave 
     * proporcionó. Devuelve un valor de clave específico si se proporciona una clave y el 
     * se encuentra el valor, o nulo si no se encuentra la clave.
     */
    public function getMetadata(?string $key = null): mixed
    {
        if (!is_resource($this->stream)) {
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
