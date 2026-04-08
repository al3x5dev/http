# Headers
The Trait `Headers` provides functionality related to the headers of an HTTP message. It is used only by the [Mk4U\Http\Request.php](https://github.com/al3x5dev/http/blob/main/docs/request.md) and [Mk4U\Http\Response.php](https://github.com/al3x5dev/http/blob/main/docs/response.md) classes.

## Protocol Version

### `getProtocolVersion()`
Returns the HTTP protocol version.
```php
$request->getProtocolVersion();
// or
$response->getProtocolVersion();
// returns "HTTP/1.1", "HTTP/2.0", etc.
```

### `setProtocolVersion(?string $version = null)`
Sets the HTTP protocol version. Valid versions are: '1.0', '1.1', '2.0', '3.0'.

```php
$request->setProtocolVersion('1.1');
// or
$response->setProtocolVersion('2.0');
```

## Headers Methods

## `getHeaders`
This method returns an array with all the headers of the response.

```php
$request->getHeaders();
// or
$response->getHeaders();
```

## `getHeader`
This method returns the value of a specific header as an array.

**Parameters:**
- `$name` (string): The name of the header.

```php
$request->getHeader('Content-Type');
// returns ["application/json"]

$response->getHeader('Cache-Control');
// returns ["max-age=3600"]
```

## `getHeaderLine`
This method returns the value of a specific header as a comma-separated string (useful for headers with multiple values).

**Parameters:**
- `$name` (string): The name of the header.

```php
$request->getHeaderLine('Accept');
// returns "text/html, application/json"

$response->getHeaderLine('Set-Cookie');
// returns "cookie1=value1, cookie2=value2"
```

## `hasHeader`
This method checks if a specific header exists in the response. Returns a boolean value.

```php
$request->hasHeader('Content-Type');
// returns true or false

$response->hasHeader('X-Custom-Header');
// returns true or false
```

## `setHeader`
This method sets the value of a specific header.

**Parameters:**
- `$name` (string): The name of the header.
- `$value` (string|array): The value of the header.

```php
$name='cache-control';
$value= 'max-age=300, s-maxage=300';
// or
$value=['max-age=300', 's-maxage=300'];

$request->setHeader($name, $value);
// or
$response->setHeader($name, $value);
```

## `setHeaders`
This method sets all the headers of the response.

**Parameters:**
- `$headers` (array): An associative array with the headers.

```php
$headers=[
    'content-type'=> 'text/html',
    'cache-control'=> 'max-age=300, s-maxage=300'
];
$request->setHeaders($headers);
// or
$response->setHeaders($headers);
```

## `addHeader`
This method adds a header to the response. If the header already exists, the value is added at the end.

**Parameters:**
- `$name` (string): The name of the header.
- `$value` (string|array): The value of the header.

```php
$request->addHeader('Accept', 'application/json');
// or
$response->addHeader('Set-Cookie', 'cookie2=value2');
```

## `removeHeader`
This method removes a header from the response.

**Parameters:**
- `$name` (string): The name of the header to remove.

```php
$request->removeHeader('X-Debug-Header');
// or
$response->removeHeader('X-Cache-Header');
```