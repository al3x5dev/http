# UploadedFile
This `UploadedFile` class allows you to interact with files uploaded to the server.

## Usage

Creating a new UploadedFile object
```php
$files=new Mk4U\Http\UploadedFile([
    $_FILES['myfile']['name'],
    $_FILES['myfile']['type'],
    $_FILES['myfile']['tmp_name'],
    $_FILES['myfile']['error'],
    $_FILES['myfile']['size']
])
// or
$files=$request->files();
```

### Method `UploadedFile::moveTo(string $targetPath)`.
This method moves the uploaded file to a new location

**Parameters:**
- $targetPath (string): Path to which the uploaded file will be moved.

For all the examples we will use the objects returned from the [Request::files()](https://github.com/al3x5dev/http/blob/dev/docs/request.md#method-requestfiles) method.
```php
$files=$request->files();

$file->moveTo(__DIR__);
```

### Method `UploadedFile::getSize()`.
This method retrieves the size in bytes of the uploaded file or null if the size is not available.

```php
$files->getSize();
// return 8103
```

### Method `UploadedFile::moveto(string $targetPath)`.
This method retrieves the error associated with the uploaded file. It returns one of the PHP `UPLOAD_ERR_XXX` constants representing the error associated with the uploaded file.

```php
$files->getError();
// return 0
```

### Method `UploadedFile::setFilename(string $filename)`.
This method sets a new file name

```php
$files->setFilename('document.docx');
```

### Method `UploadedFile::getFilename()`.
This method retrieves the file name sent by the client.

```php
$files->getFilename();
// return document.docx
```

### Method `UploadedFile::getMediaType()`.
This method retrieves the type of media sent by the client.

```php
$files->getMediaType();
// return "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
```

### Method `UploadedFile::uploadOk()`.
This method checks if the file was loaded correctly. Returns true if the file was loaded successfully (no errors), otherwise it returns false.

```php
$files->uploadOk();
// return true
```

### Method `UploadedFile::getStream()`.
This method returns a Stream object representing the uploaded file content. Throws RuntimeException if the file has an error or has already been moved.

```php
$stream = $files->getStream();
echo $stream->getContents();
```

### Method `UploadedFile::getError()`.
This method retrieves the error code associated with the upload. Returns one of the PHP `UPLOAD_ERR_XXX` constants.

```php
$files->getError();
// return UPLOAD_ERR_OK (0) on success
// return UPLOAD_ERR_INI_SIZE (1) if exceeds upload_max_filesize
// return UPLOAD_ERR_FORM_SIZE (2) if exceeds MAX_FILE_SIZE
// return UPLOAD_ERR_PARTIAL (3) if only partially uploaded
// return UPLOAD_ERR_NO_FILE (4) if no file was uploaded
```

### Method `UploadedFile::getClientFilename()`.
This method retrieves the original filename as sent by the client (the "name" field from the form).

```php
$files->getClientFilename();
// return "document.docx"
```

### Method `UploadedFile::getClientMediaType()`.
This method retrieves the media type (MIME type) as sent by the client.

```php
$files->getClientMediaType();
// return "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
```

### Method `UploadedFile::getExtension(?string $filename)`.
This method extracts the file extension from the filename.

**Parameters:**
- `$filename` (string|null): The filename to extract extension from. If null, uses getClientFilename().

```php
$files->getExtension();
// return "docx"

$files->getExtension('archive.tar.gz');
// return "tar.gz" (handles compound extensions)
```

### Method `UploadedFile::getBasename(?string $filename)`.
This method extracts the filename without extension.

**Parameters:**
- `$filename` (string|null): The filename to extract basename from. If null, uses getClientFilename().

```php
$files->getBasename();
// return "document"

$files->getBasename('archive.tar.gz');
// return "archive.tar"
```

### Method `UploadedFile::isMoved()`.
This method checks if the file has been successfully moved to its destination.

```php
$file->moveTo('/uploads/');
$file->isMoved();
// return true
```