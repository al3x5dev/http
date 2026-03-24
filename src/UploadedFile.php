<?php

namespace Mk4U\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * Uploaded File class
 * 
 * Implementa PSR-7 UploadedFileInterface
 * @see https://www.php-fig.org/psr/psr-7/#34-psrhttpmessageuploadedfileinterface
 */
class UploadedFile implements UploadedFileInterface
{
    private ?StreamInterface $stream = null;
    private bool $moved = false;

    public function __construct(
        private ?string $name = null,
        private ?string $type = null,
        public string $tmp_name,
        private int $error,
        private ?int $size
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->tmp_name = $tmp_name;
        $this->error = $error;
        $this->size = $size;
    }

    /**
     * Mover el archivo subido a una nueva ubicación.
     *
     * Utilice este método como alternativa a move_uploaded_file(). Este método
     * garantizado para funcionar tanto en entornos SAPI como no SAPI.
     * Las implementaciones deben determinar en qué entorno se encuentran y utilizar el 
     * método método apropiado (move_uploaded_file(), rename(), o una operación
     * para realizar la operación.
     *
     * $targetPath puede ser una ruta absoluta o relativa. Si es una
     * relativa, la resolución debe ser la misma que la usada por la función rename()
     * de PHP.
     *
     * El archivo o flujo original DEBE ser eliminado al finalizar.
     *
     * Si este método es llamado más de una vez, cualquier llamada subsecuente DEBE lanzar una excepción.
     *
     * Cuando se usa en un entorno SAPI donde $_FILES está poblado, cuando se escribe
     * archivos a través de moveTo(), is_uploaded_file() y move_uploaded_file() DEBERÍAN ser
     * usadas para asegurar que los permisos y el estado de subida son verificados correctamente.
     *
     * Si desea pasar a un flujo, utilice getStream(), ya que las operaciones SAPI
     * no pueden garantizar la escritura en destinos de flujo.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Ruta a la que mover el fichero subido.
     * @throws \InvalidArgumentException si el $targetPath especificado no es válido.
     * @throws \RuntimeException en cualquier error durante la operación de mover, o en
     * la segunda o subsiguiente llamada al método.
     */
    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new \RuntimeException('File has already been moved');
        }

        if (!$this->uploadOk()) {
            throw new RuntimeException("An error occurred during the move operation.");
        }

        if (empty($targetPath)) {
            throw new InvalidArgumentException('Invalid path for the movement operation, must be a non-empty string.');
        }

        move_uploaded_file($this->tmp_name, "$targetPath/{$this->getClientFilename()}");
        
        $this->moved = true;
    }

    /**
     * Recuperar una transmisión que represente el archivo cargado.
     *
     * Este método DEBE devolver una instancia de StreamInterface, que representa la
     * archivo cargado. El propósito de este método es permitir la utilización de PHP nativo
     * funcionalidad de flujo para manipular la carga de archivos, como
     * stream_copy_to_stream() (aunque el resultado tendrá que ser decorado en un
     * envoltura de flujo de PHP nativo para trabajar con tales funciones).
     *
     * Si el método moveTo() se ha llamado anteriormente, este método DEBE aumentar
     * Una excepción.
     *
     * @return StreamInterface Representación del archivo cargado.
     * @throws \RuntimeException en los casos en que no hay flujo disponible o puede ser
     * creado.
     */
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new \RuntimeException('File has already been moved');
        }
        
        if ($this->stream === null) {
            if ($this->tmp_name === '' || !is_file($this->tmp_name)) {
                throw new \RuntimeException('No stream available');
            }
            $this->stream = new Stream($this->tmp_name, 'r');
        }
        
        return $this->stream;
    }

    /**
     * Recupera el tamaño del archivo.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Recupera el error asociado con el archivo subido.
     *
     * El valor de retorno DEBE ser una de las constantes UPLOAD_ERR_XXX de PHP.
     *
     * Si el archivo fue subido con éxito, este método DEBE devolver
     * UPLOAD_ERR_OK.
     *
     * Las implementaciones DEBERÍAN devolver el valor almacenado en la clave "error" de
     * el archivo en el array $_FILES.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int Una de las constantes UPLOAD_ERR_XXX de PHP.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Recuperar el nombre de archivo enviado por el cliente.
     *
     * No confíe en el valor devuelto por este método. Un cliente podría enviar
     * un nombre de archivo malicioso con la intención de corromper o hackear su
     * aplicación.
     *
     * Las implementaciones DEBERÍAN devolver el valor almacenado en la clave "name" de
     * el archivo en el array $_FILES.
     * 
     * @return string|null El nombre del archivo enviado por el cliente o null si no se proporcionó.
     */
    public function getClientFilename(): ?string
    {
        return $this->name;
    }

    /**
     * Alias para compatibilidad hacia atrás.
     * @deprecated Usar getClientFilename() en su lugar
     */
    public function getFilename(): ?string
    {
        return $this->getClientFilename();
    }

    /**
     * Establece un nuevo nombre de archivo.
     *
     * No confíe en el valor devuelto por este método. Un cliente podría enviar
     * un nombre de archivo malicioso con la intención de corromper o hackear su
     * aplicación.
     *
     * Las implementaciones DEBERÍAN devolver el valor almacenado en la clave "name" de
     * el archivo en el array $_FILES.
     */
    public function setFilename(string $filename): void
    {
        $ext= explode('.',$this->getFilename());
        $this->name = "$filename.".end($ext);
    }

    /**
     * Recupera el tipo de medio enviado por el cliente.
     *
     * No confíe en el valor devuelto por este método. Un cliente podría enviar
     * un tipo de medio malicioso con la intención de corromper o hackear su
     * aplicación.
     *
     * Las implementaciones DEBERÍAN devolver el valor almacenado en la clave "type" de
     * el archivo en el array $_FILES.
     *
     * @return string|null El tipo de medio enviado por el cliente o null si no se ha proporcionado ninguno.
     * fue proporcionado.
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * Alias para compatibilidad hacia atrás.
     * @deprecated Usar getClientMediaType() en su lugar
     */
    public function getMediaType(): ?string
    {
        return $this->getClientMediaType();
    }

    /**
     * Verifica si el fichero se cargo correctamente
     */
    public function uploadOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }
}
