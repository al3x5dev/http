# Uri
This `Uri` class represents a URI (Uniform Resource Identifier) and provides methods to manipulate its components.

## Usage

Creating a new object
```php
use Mk4U\Http\Uri;

$uri=new Uri();
// or
$uri=new Uri('http://john:xyz%2A12@example.org:8080/en/download?name=param#footer');
```

### Returns object converted to a string
The `__toString` magic method returns the URI converted to a string.
```php
echo $uri;
// return "http://john:xyz%2A12@example.org:8080/en/download?name=param#footer"
```

### Returns information for object debugging.
The `__debugInfo` magic method returns the URI representation as an array for debugging purposes.
```php
var_dump($uri);
/* return [
    "schema"=> "http",
    "user-info"=> "john:xyz%2A12",
    "host"=> "example.org",
    "port"=> 8080,
    "path"=> "/en/download",
    "query"=> "name=param",
    "fragment"=> "footer",
]*/
```

### Method `Uri::setScheme(string $scheme = '')`.
This method is in charge of setting the URL scheme

**Parameters:**
- `$scheme` (string): The scheme you want to set for the URL.

```php
$uri->setScheme($scheme);
```

### Method `Uri::setUserInfo(string $user, ?string $password = NULL)`.
This method sets the user information in the URI to the format "user:password" if the password is provided. If the password is not provided, only the username is set.

**Parameters:**
- `$user` (string): The user name to be used to obtain authority.
- `$password` (string|null): The password associated with the user. It is optional.

```php
$uri->setUserInfo($user, $password);
```

### Method `Uri::setHost(string $host = '')`.
This method is responsible for setting the host of the URL.

**Parameters:**
- `$host` (string): The host to be set for the URL.

```php
$uri->setHost($host);
```

### Method `Uri::setPort(?int $port = NULL)`.
This method is responsible for setting the port of the URL. If the port is not valid, it throws an `InvalidArgumentException`.

**Parameters:**
- `$port` (string): The port to be set for the URL.

```php
$uri->setPort($port);
```

### Method `Uri::setPath(string $path = '/')`.
This method is responsible for setting the URL path.

**Parameters:**
- `$path` (string): The path to set for the URL.

```php
$uri->setPath($path);
```

### Method `Uri::setQuery(string $query = '')`.
This method is responsible for setting the URL queries.

**Parameters:**
- `$query` (string): The queries you want to set for the URL.

```php
$uri->setQuery($query);
```

### Method `Uri::setFragment(string $fragment = '')`.
This method takes care of setting the URL fragment.

**Parameters:**
- `$fragment` (string): The fragment you want to set for the URL.

```php
$uri->setFragment($fragment);
```

### Method `Uri::getScheme()`.
This method retrieves the schema component of the URI.
```php
$uri->getScheme();
// return "http"
```

### Method `Uri::getHost()`.
This method retrieves the host component of the URI.
```php
$uri->getHost();
// return "example.org"
```

### Method `Uri::getAuthority()`.
This method retrieves the authority component of the URI.

```php
$uri->getAuthority();
```

### Method `Uri::getUserInfo()`.
This method retrieves the user information component of the URI.

```php
$uri->getUserInfo();
// return "john:xyz%2A12"
```

### Method `Uri::getPort()`.
This method retrieves the port component of the URI.

It checks if the schema is empty and if the port is the default port for that schema. Returns the port if it is not the default port, otherwise returns null.
```php
$uri->getPort();
// return 8080
```

### Method `Uri::getPath()`.
This method retrieves the path component of the URI.

```php
$uri->getPath();
// return "/en/download"
```

### Method `Uri::getQuery(bool $array = false)`.
This method retrieves the query string of the URI.

**Parameters:**
- `$array` (bool): optional. If set to true, the query is returned as an array.

```php
$uri->getQuery();
// return  "name=param"

$uri->getQuery(true);
// return  ["name"=>"param"]
```

### Method `Uri::getQueryToArray()`.
This method parses the query string and returns it as an associative array.
```php
$uri->getQueryToArray();
// return ["name" => "param"]
```

### Method `Uri::getFragment()`.
This method retrieves the fragment component of the URI.

```php
$uri->getFragment();
// return "footer"
```

### Method `Uri::fromString(string $uri)`.
This static method creates a new Uri instance from a URI string. Throws InvalidArgumentException if the URI cannot be parsed.

```php
$uri = Uri::fromString('https://example.com/path?query=value#fragment');
// Returns a fully parsed Uri object
```

### Method `Uri::getAuthority()`.
This method retrieves the authority component of the URI (user@host:port).

```php
$uri->getAuthority();
// return "john:password@example.com:8080"
```

### Method `Uri::getUserInfo()`.
This method retrieves the user information component of the URI.

```php
$uri->getUserInfo();
// return "john:password"
```

### Method `Uri::getPort()`.
This method retrieves the port component of the URI. Returns null if it's the default port for the scheme.

```php
$uri->getPort();
// return 8080 or null for default ports
```
