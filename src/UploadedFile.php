<?php

namespace Mk4U\Http;

use InvalidArgumentException;
use RuntimeException;

/**
 * Uploaded File class
 */
class UploadedFile
{

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
        if (!$this->uploadOk()) {
            throw new RuntimeException("An error occurred during the move operation.");
        }

        if (empty($targetPath)) {
            throw new InvalidArgumentException('Invalid path for the movement operation, must be a non-empty string.');
        }

        move_uploaded_file($this->tmp_name, "$targetPath/{$this->getFilename()}");
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
     */
    public function getFilename(): ?string
    {
        return $this->name;
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
    public function getMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * Verifica si el fichero se cargo correctamente
     */
    public function uploadOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }
}
