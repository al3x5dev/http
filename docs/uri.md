# Uri

The `Uri` class represents a URI (Uniform Resource Identifier) and provides an immutable API to manipulate its components. Built on PHP 8.5's native `Uri\Rfc3986\Uri`.

> **Requires PHP 8.5+** — uses the built-in RFC 3986 parser.

## Usage

Creating a new object:

```php
use Mk4U\Http\Uri;

$uri = new Uri();
// or
$uri = new Uri('http://john:xyz%2A12@example.org:8080/en/download?name=param#footer');
```

### `__toString(): string`

Returns the normalized URI string:

```php
echo $uri;
// http://john:xyz%2A12@example.org:8080/en/download?name=param#footer
```

### `__debugInfo(): array`

Returns URI components for debugging:

```php
var_dump($uri);
/*
[
    "scheme"   => "http",
    "userInfo" => "john:xyz%2A12",
    "host"     => "example.org",
    "port"     => 8080,
    "path"     => "/en/download",
    "query"    => "name=param",
    "fragment" => "footer",
]
*/
```

## Immutable setters (PSR-7 style)

All `with*` methods return a **new instance** — the original is not modified.

### `withScheme(string $scheme): static`

```php
$uri = $uri->withScheme('https');
```

### `withUserInfo(string $user, ?string $password = null): static`

```php
$uri = $uri->withUserInfo('john', 'secret');
```

### `withHost(string $host): static`

```php
$uri = $uri->withHost('example.org');
```

### `withPort(?int $port): static`

Returns `null` if the port matches the default for the scheme (80 for http, 443 for https, etc.).

```php
$uri = $uri->withPort(8080);
```

### `withPath(string $path): static`

```php
$uri = $uri->withPath('/en/download');
```

### `withQuery(string $query): static`

```php
$uri = $uri->withQuery('name=param');
```

### `withFragment(string $fragment): static`

```php
$uri = $uri->withFragment('footer');
```

## Getters

### `getScheme(): string`

Returns the scheme or empty string if not set.

```php
$uri->getScheme(); // "http"
```

### `getHost(): string`

Returns the host or empty string if not set.

```php
$uri->getHost(); // "example.org"
```

### `getPort(): ?int`

Returns the port or `null` if it's the default port for the scheme.

```php
$uri->getPort(); // 8080 or null for default ports
```

### `getPath(): string`

```php
$uri->getPath(); // "/en/download"
```

### `getQuery(): string`

Returns the raw query string or empty string if not set.

```php
$uri->getQuery(); // "name=param"
```

### `getQueryToArray(): array`

Parses the query string into an associative array.

```php
$uri->getQueryToArray(); // ["name" => "param"]
```

### `getFragment(): string`

```php
$uri->getFragment(); // "footer"
```

### `getAuthority(): string`

Returns the authority in `[user-info@]host[:port]` format, or empty string if no host.

```php
$uri->getAuthority(); // "john:xyz%2A12@example.org:8080"
```

### `getUserInfo(): string`

Returns the user info in `username[:password]` format, or empty string.

```php
$uri->getUserInfo(); // "john:xyz%2A12"
```

### `getPassword(): ?string`

Returns the raw password component from the URI, or null if not present.

```php
$uri->getPassword(); // "xyz%2A12"
```

## Comparison

### `equals(Uri $uri, bool $fragment = false): bool`

Checks if two URIs are equivalent. By default the fragment is included in the comparison; pass `true` to exclude it.

```php
$uri->equals($otherUri);              // includes fragment
$uri->equals($otherUri, true);        // excludes fragment
```

## Chaining example

```php
$uri = (new Uri('http://example.com'))
    ->withScheme('https')
    ->withPort(8443)
    ->withPath('/api/v1/users')
    ->withQuery('page=2');

echo $uri; // https://example.com:8443/api/v1/users?page=2
```
