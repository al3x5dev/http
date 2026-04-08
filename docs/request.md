# Request
The `Request` class allows you to interact with the data coming into your application.

## Usage

There are two ways to create a new Request object, you can create a request based on PHP's superglobal variables, or simply simulate a request:

### Simulating a request
When you simulate a request you must pass as parameters the http method, the uri or a [Uri object](https://github.com/al3x5dev/http/blob/main/docs/uri.md), the headers (optional), the request body (optional) and the protocol version (optional).
```php
require __DIR__ . '/vendor/autoload.php';

$method = 'POST';
$uri = 'http://example.com/api/resource';
$headers = ['Content-Type' => 'application/json'];
$body = json_encode(['key' => 'value']);
$version = '1.1';

$request=new Mk4U\Http\Request($method, $uri, $headers, $body, $version);
```

### Create request based on PHP global variables.
```php
require __DIR__ . '/vendor/autoload.php';

$request=Mk4U\Http\Request::create();
```

### Returns information for object debugging.
The magic method `__debugInfo` returns an array with information about the HTTP request, including the method, URI, protocol version, headers and content.
```php
var_dump($request);
```

### Method `Request::server(string $index)`.
This static method returns a specific value from the `$_SERVER` array or the entire array if no index is provided. It is passed as a parameter the Index of the `$_SERVER` array from which you want to get the value. Returns the value corresponding to the index provided in `$_SERVER`, or the entire array if no index is specified. This method is case insensitive.
```php
$request->server();
/* return [
  "HTTP_HOST" => "localhost"
  "REQUEST_METHOD" => "GET"
  "REQUEST_URI" => "/website/"
  "SCRIPT_NAME" => "/website/index.php"
  ...
]*/

// This Method Is Case Insensitive
$request->server('REMOTE_ADDR');
$request->server('remote_addr');
$request->server('REmoTe_ADdR');
// return 127.0.0.1
```

### Method `Request::getTarget()`.
This method gets the path of the current URI and assigns it to the target property. Returns the path of the current URI stored in the target property, or '/' if no path has been assigned.
```php
$request->getTarget();
// return "/" or "/website/"
```

### Method `Request::setMethod(string $method)`.
This method sets the HTTP method for the current request. Receives as parameter the HTTP method to be set. Returns a copy of the Request object with the updated HTTP method. This method is case insensitive.
```php
// This Method Is Case Insensitive.
$request->setMethod('GET');
$request->setMethod('get');
$request->setMethod('Get');
```

### Method `Request::hasMethod(string $method)`.
This method checks if the HTTP method of the current request matches the provided method. Receives as parameter the HTTP method to be checked. Returns true if the method of the current request matches the provided method, otherwise returns false. This method is case insensitive.
```php
// This Method Is Case Insensitive.
$request->hasMethod('GET');
$request->hasMethod('get');
$request->hasMethod('Get');
// return true
```

### Method `Request::getMethod()`.
This method returns a string with the HTTP method used in the request.
```php
$request->getMethod();
// return "GET"
```

### Method `Request::setUri($uri, $preserv_host = false)`.
This method sets the [Uri object](https://github.com/al3x5dev/http/blob/main/docs/uri.md) for the current request and optionally preserves the host in the request headers. Returns a copy of the Request object with the updated [Uri](https://github.com/al3x5dev/http/blob/main/docs/uri.md) object and, optionally, the preserved host in the headers.

**Parameters:**
- `$uri` (Uri): the [Uri object](https://github.com/al3x5dev/http/blob/main/docs/uri.md) to set for the request.
- `$preserv_host` (bool): Indicates whether to preserve the host in the request 
```php
$request->setUri($uri);
// or
$request->setUri($uri,true);
```

### Method `Request::getUri()`.
This method returns the [Uri object](https://github.com/al3x5dev/http/blob/main/docs/uri.md) associated with the current request.
```php
$request->getUri();
// return object(Mk4U\Http\Uri)
```

### Method `Request::isFormData()`.
This method determines if values are sent through a form in the current request. Returns true if the request uses the POST method and the content type matches the specified form content types, otherwise returns false.
```php
$request->isFormData();
// return false
```

### Method `Request::queryData($name, $default)`.
This method gets the query string parameters from the URI. If no parameter name is specified, it returns all values from the superglobal `$_GET`.
 
**Parameters:**
- `$name` (string|null): the name of the parameter to fetch. Default is null.
- `$default` (mixed): The default value to return if the parameter is not present. Defaults to null.
```php
$request->queryData();
/* return [
  "name" => "param"
  ...
] */

$request->queryData('name');
// return "param"

$request->queryData('unavailable','value');
// "value"
```

### Method `Request::inputData($name, $default)`.
This method retrieves the parameters provided in the request body, depending on the content type and request method. Returns the parameters from the request body, either from `$_POST` or from the request body content.

**Parameters:**
- `$name` (string|null): the name of the parameter to retrieve. Default is null.
- `$default` (mixed): The default value to return if the parameter is not present. Defaults to null.
```php
$request->inputData();
/* return [
  "name" => "param"
  ...
] */

$request->inputData('name');
// return "param"

$request->inputData('unavailable','value');
// "value"
```

### Method `Request:: jsonData(bool $assoc = true)`.
This method returns the decoded JSON content if the request content type is *application/json*. Returns the decoded JSON content in array, object or null form.

Parameter:
- `$assoc` (bool): indicates whether to return an associative array. Defaults to true.
```php
$request->jsonData();
/* return [
  "name" => "param"
  ...
] */

$request->jsonData(false);
// return object(name)
```

### Method `Request::rawData()`.
This method returns the raw contents of the request body.
```php
$request->rawData();
// return  "name=param"
```

### Method `Request::files()`.
This method returns an array containing the files uploaded to the server in the current request stored in the [UploadedFile](https://github.com/al3x5dev/http/blob/main/docs/uploadedfile.md) or an empty array if there are no files.
```php
$request->files();
/* return [
  "myfile" => Mk4U\Http\UploadedFile {
    -name: "My Document.docx"
    -type: "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    +tmp_name: "C:\xampp\tmp\php35E1.tmp"
    -error: 0
    -size: 18289
  }
]*/ 
```

### Method `Request::getMethod()`.
This method returns the HTTP method used in the request as an uppercase string.
```php
$request->getMethod();
// return "GET", "POST", "PUT", etc.
```

### Method `Request::getUri()`.
This method returns the [Uri object](https://github.com/al3x5dev/http/blob/main/docs/uri.md) associated with the current request.
```php
$request->getUri();
// return object(Mk4U\Http\Uri)
```

### Method `Request::setUri(Uri $uri, bool $preserveHost = false)`.
This method sets the [Uri object](https://github.com/al3x5dev/http/blob/main/docs/uri.md) for the current request. Optionally preserves the host header.

**Parameters:**
- `$uri` (Uri): The Uri object to set.
- `$preserveHost` (bool): If true, preserves the existing Host header. Default is false.

```php
$uri = new Uri('https://example.com/path');
$request->setUri($uri);

// With host preservation
$request->setUri($uri, true);
```

### Method `Request::getTarget()`.
This method returns the target path of the request (the URI path).
```php
$request->getTarget();
// return "/path/to/resource" or "/" if empty
```

### Method `Request::isFormData()`.
This method determines if the request contains form data (application/x-www-form-urlencoded or multipart/form-data with POST method).
```php
$request->isFormData();
// return true or false
```
