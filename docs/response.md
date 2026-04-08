# Response
This `Response` class allows you to output data from your application in different ways.

## Usage

Creating a new response
```php
use Mk4U\Http\Response;

$response=new Response(
  'Hello World!',
  headers:[
    'content-type' => 'text/plain'
    ]
);
return $response;
// or
$response=new Response();
return $response->plain('Hello World!');
// Both implementations return "Hello World!"" and the default status code is 200.
```

Can also create responses for different types of content and [status codes](https://github.com/al3x5dev/http/blob/main/docs/status.md).
```php
$response=new Response();

// You can specify the status code and the reason phrase via the Status enum or as an array.
$status=Mk4U\Http\Status::MovedPermanently;
// or
$status=[301,'Moved Permanently'];


return $response->html('<h1>Hello World!</h1>', $status,['Retry-After'=>'Retry-After: 120'],'1.2');

return $response->json(
  '{"message":"Hello World!"}',
   $status,
   ['Content-MD5'=>'d41d8cd98f00b204e9800998ecf8427e'],
   '1.0'
  );
```

### Returns information for object debugging.
The magic method `__debugInfo` returns an array with information about the HTTP response, including the protocol version, code, phrase, headers and body.
```php
var_dump($response);
```

### Method `Response::getStatusCode()`.
This method returns the HTTP response status code.
```php
$response->getStatusCode();
// 200
```

### Method `Response::setStatus(int $code, string $reasonPhrase = '')`.
This method sets the status code and optionally, the HTTP response reason phrase. The `InvalidArgumentException` is thrown if the status code is invalid.

**Parameters:**
- `$code` (int): The 3-digit status code to be set.
- `$reasonPhrase` (string): The reason phrase to associate with the status code. If not provided, a default value can be used.

```php
$response->setStatus(200, 'OK');
// or
$response->setStatus(Mk4U\Http\Status::Ok->value);
```

### Method `Response::getReasonPhrase()`.
This method returns the reason phrase associated with the HTTP response status code.

```php
$response->getReasonPhrase();
// return "Ok"
```

### Method `Response::getBody()`.
This method returns the message body of the response.

```php
$body=$response->getBody();
```

### Method `Response::setBody(mixed $body)`.
This method sets the message body of the response.

```php
$response->setBody($content);
```

### Method `Response::json(array|object $content, Status|array $status = Status::Ok, array $headers = [])`.
This method returns the message body in JSON format.

**Parameters:**
- `$content` (array|object): The content to be converted to JSON.
- `$status` (Status|array): The status of the response (default is Ok).
- `$headers` (array): The headers of the response.

```php
Response::json('{"message":"Hello World!"}', $status, $headers);
```

### Method `Response::plain(string $content, Status|array $status = Status::Ok, array $headers = [])`.
This method returns the message body in plain text format.

**Parameters:**
- `$content` (string): The content in plain text format.
- `$status` (Status|array): The status of the response (default is Ok).
- `$headers` (array): The headers of the response

```php
Response::plain('Hello World!', $status, $headers);
```

### Method `Response::html(string $content, Status|array $status = Status::Ok, array $headers = [])`.
This method returns the message body in HTML format.

**Parameters:**
- `$content` (string): The content in HTML format.
- `$status` (Status|array): The status of the response (default is Ok).
- `$headers` (array): The headers of the response

```php
Response::html('<h1>Hello World!</h1>', $status, $headers);
```

### Method `Response::xml(string $content, Status|array $status = Status::Ok, array $headers = [])`.
This method returns the message body in XML format.

**Parameters:**
- `$content` (string): The content in XML format.
- `$status` (Status|array): The status of the response (default is Ok).
- `$headers` (array): The headers of the response

```php
$xml=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<note>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
  <body>Don't forget me this weekend!</body>
</note>
XML;
Response::xml($xml, $status, $headers);
```

### Method `Response::download(string $filePath, ?string $filename = null, array $headers = [], bool $display = false)`.
This method generates a response that forces the user's browser to download the file at the given path.

**Parameters:**
- `$filePath` (string): Absolute path to the file to download.
- `$filename` (string|null): Custom filename for the download (optional, defaults to original filename).
- `$headers` (array): Additional HTTP headers (optional).
- `$display` (bool): If true, displays file inline instead of forcing download (default: false).

```php
// Basic download
Response::download('/path/to/file.pdf');

// With custom filename
Response::download('/path/to/file.pdf', 'my-document.pdf');

// With custom headers
Response::download('/path/to/file.pdf', null, ['Cache-Control' => 'no-cache']);

// Display file inline (in browser)
Response::download('/path/to/image.png', display: true);
```

### Method `Response::file(string $filePath, array $headers = [])`.
This method displays a file directly in the user's browser instead of downloading it (inline).

**Parameters:**
- `$filePath` (string): Absolute path to the file.
- `$headers` (array): Additional HTTP headers (optional).

```php
// Display image in browser
Response::file('/path/to/image.png');

// With custom headers
Response::file('/path/to/image.png', ['Cache-Control' => 'public, max-age=3600']);
```

### Method `Response::streamDownload($stream, ?string $filename = null, ?int $filesize = null, array $headers = [])`.
This method downloads a file from a stream, useful for large files to avoid loading them entirely into memory.

**Parameters:**
- `$stream` (resource): A readable stream resource.
- `$filename` (string|null): Custom filename for the download (optional).
- `$filesize` (int|null): Size of the file in bytes (optional).
- `$headers` (array): Additional HTTP headers (optional).

```php
// Download from stream
$stream = fopen('/path/to/large-file.zip', 'r');
Response::streamDownload($stream, 'download.zip', filesize('/path/to/large-file.zip'));

// Or from a URL stream
$stream = fopen('http://example.com/file.zip', 'r');
Response::streamDownload($stream, 'file.zip');
```

### MIME Type Detection
The `Response::download()`, `Response::file()`, and `Response::streamDownload()` methods automatically detect the MIME type based on the file extension:

```php
$response = Response::download('/path/to/document.pdf');
// Content-Type: application/pdf

$response = Response::download('/path/to/image.png');
// Content-Type: image/png

$response = Response::download('/path/to/data.json');
// Content-Type: application/json

// Unknown extensions default to: application/octet-stream
```