<?php

namespace Mk4U\Http;

use InvalidArgumentException;
use RuntimeException;

/**
 * Uploaded File class
 */
class UploadedFile
{
    private ?Stream $stream = null;
    private bool $moved = false;

    public function __construct(
        private ?string $name = null,
        private ?string $type = null,
        private string $tmp_name,
        private int $error,
        private ?int $size
    ) {}

    /**
     * Mover el archivo subido a una nueva ubicación.
     *
     * Utilice este método como alternativa a move_uploaded_file(). Este método
     * garantizado para funcionar tanto en entornos SAPI como no SAPI.
     * Las implementaciones deben determinar en qué entorno se encuentran y utilizar el
     * método apropiado (move_uploaded_file(), rename(), o una operación
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
     * @throws InvalidArgumentException si el $targetPath especificado no es válido.
     * @throws RuntimeException en cualquier error durante la operación de mover, o en
     * la segunda o subsiguiente llamada al método.
     */
    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new RuntimeException('File has already been moved');
        }

        if (!$this->uploadOk()) {
            throw new RuntimeException("An error occurred during the move operation.");
        }

        if (empty($targetPath)) {
            throw new InvalidArgumentException('Target path must be a non-empty string.');
        }

        if (!is_dir($targetPath)) {
            throw new InvalidArgumentException('Target path must be a valid directory.');
        }

        $filename = $this->sanitizeFilename($this->getFilename());
        $targetPath = rtrim($targetPath, '/\\');

        if (!move_uploaded_file($this->tmp_name, "$targetPath/$filename")) {
            throw new RuntimeException('Error moving uploaded file.');
        }

        $this->moved = true;
    }

    /**
     * Sanitiza el nombre del archivo para prevenir path traversal.
     */
    private function sanitizeFilename(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return 'uploaded_file';
        }

        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);
        $filename = basename($filename);

        if ($filename === '' || $filename === '.') {
            return 'uploaded_file';
        }

        return $filename;
    }

    /**
     * Recupera una transmisión que represente el archivo cargado.
     *
     * @return Stream Representación del archivo cargado.
     * @throws RuntimeException si no hay flujo disponible.
     */
    public function getStream(): Stream
    {
        if ($this->moved) {
            throw new RuntimeException('File has already been moved');
        }

        if ($this->stream === null) {
            if ($this->tmp_name === '' || !is_file($this->tmp_name)) {
                throw new RuntimeException('No stream available');
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
     * @return int Una de las constantes UPLOAD_ERR_XXX de PHP.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Recuperar el nombre de archivo enviado por el cliente.
     *
     * No confíe en el valor devuelto por este método.
     */
    public function getFilename(): ?string
    {
        return $this->name;
    }

    /**
     * Alias para compatibilidad con nomenclatura PSR-7.
     */
    public function getClientFilename(): ?string
    {
        return $this->getFilename();
    }

    /**
     * Establece un nuevo nombre de archivo.
     */
    public function setFilename(string $filename): void
    {
        $extension = $this->getExtension($this->getFilename());

        if ($extension !== null) {
            $this->name = "$filename.$extension";
        } else {
            $this->name = $filename;
        }
    }

    /**
     * Extrae la extensión del nombre de archivo.
     *
     * @param string|null $filename Nombre del archivo
     * @return string|null Extensión o null si no hay
     */
    public function getExtension(?string $filename): ?string
    {
        if ($filename === null || $filename === '') {
            return null;
        }

        $filename = basename($filename);

        if (strpos($filename, '.') === false) {
            return null;
        }

        $extensions = ['tar.gz', 'tar.bz2', 'tar.xz', 'tar.zst'];
        $filenameLower = strtolower($filename);

        foreach ($extensions as $ext) {
            if (str_ends_with($filenameLower, ".$ext")) {
                return $ext;
            }
        }

        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Extrae el nombre base sin extensión.
     *
     * @param string|null $filename Nombre del archivo
     * @return string Nombre base
     */
    public function getBasename(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return 'uploaded_file';
        }

        $filename = basename($filename);

        if (strpos($filename, '.') === false) {
            return $filename;
        }

        $extensions = ['tar.gz', 'tar.bz2', 'tar.xz', 'tar.zst'];
        $filenameLower = strtolower($filename);

        foreach ($extensions as $ext) {
            if (str_ends_with($filenameLower, ".$ext")) {
                return substr($filename, 0, - (strlen($ext) + 1));
            }
        }

        return pathinfo($filename, PATHINFO_FILENAME);
    }

    /**
     * Recupera el tipo de medio enviado por el cliente.
     *
     * No confíe en el valor devuelto por este método.
     */
    public function getMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * Alias para compatibilidad con nomenclatura PSR-7.
     */
    public function getClientMediaType(): ?string
    {
        return $this->getMediaType();
    }

    /**
     * Verifica si el fichero se cargo correctamente
     */
    public function uploadOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }
}
