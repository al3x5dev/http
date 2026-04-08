# UploadedFile
This `UploadedFile` class allows you to interact with files uploaded to the server.

## Usage

Creating a new UploadedFile object manually (from $_FILES):
```php
$file = new Mk4U\Http\UploadedFile(
    $_FILES['myfile']['tmp_name'],  // File path to temporary upload
    $_FILES['myfile']['size'],      // File size in bytes
    $_FILES['myfile']['name'],      // Original filename from client
    $_FILES['myfile']['type'],     // MIME type (optional)
    $_FILES['myfile']['error']      // Upload error code (optional)
);
```

Or use the `Request::files()` method which automatically creates UploadedFile objects:
```php
$files = $request->files();
// Returns array of UploadedFile objects keyed by field name
```

### Method `UploadedFile::moveTo(string $targetPath)`.
This method moves the uploaded file to a new location

**Parameters:**
- $targetPath (string): Directory path where the uploaded file will be moved.

```php
$file = $request->files()['myfile'];

$file->moveTo(__DIR__ . '/uploads/');
```

### Method `UploadedFile::getSize()`.
This method retrieves the size in bytes of the uploaded file or null if the size is not available.

```php
$file->getSize();
// return 8103
```

### Method `UploadedFile::getError()`.
This method retrieves the error code associated with the upload. Returns one of the PHP `UPLOAD_ERR_XXX` constants.

```php
$file->getError();
// return UPLOAD_ERR_OK (0) on success
// return UPLOAD_ERR_INI_SIZE (1) if exceeds upload_max_filesize
// return UPLOAD_ERR_FORM_SIZE (2) if exceeds MAX_FILE_SIZE
// return UPLOAD_ERR_PARTIAL (3) if only partially uploaded
// return UPLOAD_ERR_NO_FILE (4) if no file was uploaded
// return UPLOAD_ERR_NO_TMP_DIR (5) if missing temporary folder
// return UPLOAD_ERR_CANT_WRITE (6) if failed to write to disk
// return UPLOAD_ERR_EXTENSION (7) if upload stopped by extension
```

### Method `UploadedFile::setFilename(string $filename)`.
This method sets a new file name (preserving the original extension)

```php
$file->setFilename('document');
// If original was "image.png", becomes "image.png"
// If original was "photo", becomes "photo"
```

### Method `UploadedFile::getFilename()`.
This method retrieves the original filename sent by the client.

```php
$file->getFilename();
// return "document.docx"
```

### Method `UploadedFile::getMediaType()`.
This method retrieves the media type (MIME type) sent by the client.

```php
$file->getMediaType();
// return "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
```

### Method `UploadedFile::uploadOk()`.
This method checks if the file was uploaded correctly. Returns true if the upload was successful (no errors), otherwise returns false.

```php
$file->uploadOk();
// return true
```

### Method `UploadedFile::getStream()`.
This method returns a Stream object representing the uploaded file content.

**Throws:** RuntimeException if the file has an upload error or has already been moved.

```php
$stream = $file->getStream();
echo $stream->getContents();
```

### Method `UploadedFile::getClientFilename()`.
This method retrieves the original filename as sent by the client (alias for getFilename).

```php
$file->getClientFilename();
// return "document.docx"
```

### Method `UploadedFile::getClientMediaType()`.
This method retrieves the media type (MIME type) as sent by the client (alias for getMediaType).

```php
$file->getClientMediaType();
// return "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
```

### Method `UploadedFile::getExtension(?string $filename)`.
This method extracts the file extension from the filename.

**Parameters:**
- `$filename` (string|null): The filename to extract extension from. If null, uses getClientFilename().

```php
$file->getExtension();
// return "docx"

$file->getExtension('archive.tar.gz');
// return "tar.gz" (handles compound extensions like .tar.gz)
```

### Method `UploadedFile::getBasename(?string $filename)`.
This method extracts the filename without extension.

**Parameters:**
- `$filename` (string|null): The filename to extract basename from. If null, uses getClientFilename().

```php
$file->getBasename();
// return "document"

$file->getBasename('archive.tar.gz');
// return "archive" (handles compound extensions)
```

### Method `UploadedFile::isMoved()`.
This method checks if the file has been successfully moved to its destination.

```php
$file->moveTo('/uploads/');
$file->isMoved();
// return true
```
