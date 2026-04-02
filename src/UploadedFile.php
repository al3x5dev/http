<?php

namespace Mk4U\Http;

/**
 * Uploaded File class
 */
class UploadedFile
{
    private const ERROR_MAP = [
        UPLOAD_ERR_OK         => 'UPLOAD_ERR_OK',
        UPLOAD_ERR_INI_SIZE   => 'UPLOAD_ERR_INI_SIZE',
        UPLOAD_ERR_FORM_SIZE => 'UPLOAD_ERR_FORM_SIZE',
        UPLOAD_ERR_PARTIAL   => 'UPLOAD_ERR_PARTIAL',
        UPLOAD_ERR_NO_FILE   => 'UPLOAD_ERR_NO_FILE',
        UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
        UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
        UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION',
    ];

    private ?Stream $stream = null;
    private bool $moved = false;
    private ?string $file = null;

    public function __construct(
        private $streamOrFile,
        private ?int $size,
        private ?string $name = null,
        private ?string $type = null,
        private int $error = UPLOAD_ERR_OK
    ) {
        $this->setError($error);

        // Solo inicializar stream/file si no hay error
        if ($this->isOk()) {
            $this->setStreamOrFile($streamOrFile);
        }
    }

    /**
     * Configura el stream o archivo según el tipo de dato recibido
     * 
     * @param mixed $streamOrFile StreamInterface, string, resource o Stream
     * @throws InvalidArgumentException Si el tipo no es válido
     */
    private function setStreamOrFile($streamOrFile): void
    {
        if (is_string($streamOrFile)) {
            $this->file = $streamOrFile;
        } elseif (is_resource($streamOrFile)) {
            $this->stream = new Stream($streamOrFile);
        } elseif ($streamOrFile instanceof Stream) {
            $this->stream = $streamOrFile;
        } elseif ($streamOrFile instanceof Stream) {
            $this->stream = $streamOrFile;
        } else {
            throw new \InvalidArgumentException(
                'Invalid stream or file provided for UploadedFile'
            );
        }
    }

    /**
     * Valida y configura el código de error
     * 
     * @param int $error Código de error
     * @throws InvalidArgumentException Si el código no es válido
     */
    private function setError(int $error): void
    {
        if (!isset(self::ERROR_MAP[$error])) {
            throw new \InvalidArgumentException(
                'Invalid error status for UploadedFile: ' . $error
            );
        }
        $this->error = $error;
    }

    /**
     * Verifica si no hay error de upload
     */
    private function isOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * Valida que el archivo esté activo y listo para operar
     * 
     * @throws RuntimeException Si hay error de upload o ya fue movido
     */
    private function validateActive(): void
    {
        if (!$this->isOk()) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot retrieve stream due to upload error (%s)',
                    self::ERROR_MAP[$this->error]
                )
            );
        }

        if ($this->moved) {
            throw new \RuntimeException(
                'Cannot retrieve stream after it has already been moved'
            );
        }
    }

    /**
     * Sanitiza el nombre del archivo para prevenir path traversal.
     */
    private function sanitizeFilename(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return 'uploaded_file';
        }

        // Eliminar caracteres de control
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);

        // Obtener solo el nombre base (eliminar directorios)
        $filename = basename($filename);

        // Verificar que no sea vacío o solo puntos
        if ($filename === '' || $filename === '.') {
            return 'uploaded_file';
        }

        return $filename;
    }

    /**
     * Mover el archivo subido a una nueva ubicación.
     *
     * Este método funciona tanto en entornos SAPI como CLI.
     * En SAPI usa move_uploaded_file() para seguridad.
     * En CLI usa rename() para velocidad.
     * 
     * Utilice este método como alternativa a move_uploaded_file().
     * 
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * 
     * @param string $targetPath Ruta destino (directorio donde mover)
     * @throws InvalidArgumentException Si el path no es válido
     * @throws RuntimeException Si hay error de upload, ya fue movido, o falla el movimiento
     */
    public function moveTo(string $targetPath): void
    {
        $this->validateActive();

        // Validar que targetPath sea string no vacío
        if (empty($targetPath)) {
            throw new \InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string'
            );
        }

        // Validar que targetPath sea un directorio válido
        if (!is_dir($targetPath)) {
            throw new \InvalidArgumentException(
                'Target path must be a valid directory'
            );
        }

        // Sanitizar nombre de archivo del cliente
        $filename = $this->sanitizeFilename($this->getClientFilename());
        $targetPath = rtrim($targetPath, '/\\');
        $targetFile = "$targetPath/$filename";

        // Si tenemos archivo (no stream), mover directamente
        if ($this->file) {
            // Verificar que el archivo existe y es legible
            if (!is_file($this->file) || !is_readable($this->file)) {
                throw new \RuntimeException('Source file does not exist or is not readable');
            }

            // En CLI usamos rename, en SAPI usamos move_uploaded_file
            $this->moved = (PHP_SAPI === 'cli')
                ? rename($this->file, $targetFile)
                : move_uploaded_file($this->file, $targetFile);
        } else {
            // Es un stream - copiar contenido al destino
            $source = $this->getStream();
            $target = new Stream($targetFile, 'w');

            // Copiar stream origen a destino eficientemente
            while (!$source->eof()) {
                $data = $source->read(8192);
                if ($data !== '') {
                    $target->write($data);
                }
            }

            $this->moved = true;
        }

        // Verificar que el movimiento fue exitoso
        if (!$this->moved) {
            throw new \RuntimeException(
                sprintf('Uploaded file could not be moved to %s', $targetFile)
            );
        }
    }

    /**
     * Recupera una transmisión que represente el archivo cargado.
     *
     * @return Stream Representación del archivo cargado.
     * @throws RuntimeException si no hay flujo disponible.
     */
    public function getStream(): Stream
    {
        $this->validateActive();

        // Si ya tenemos stream, retornarlo
        if ($this->stream instanceof Stream) {
            return $this->stream;
        }

        // Es un archivo - crear LazyOpenStream para abrir solo cuando se use
        return new Stream($this->file, 'r');
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
        return $this->getClientFilename();
    }

    /**
     * Alias para compatibilidad con nomenclatura PSR-7.
     */
    public function getClientFilename(): ?string
    {
        return $this->name;
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
        return $this->getClientMediaType();
    }

    /**
     * Alias para compatibilidad con nomenclatura PSR-7.
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * Verifica si el archivo fue movido exitosamente.
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * Verifica si el fichero se cargo correctamente
     */
    public function uploadOk(): bool
    {
        return $this->isOk();
    }
}
