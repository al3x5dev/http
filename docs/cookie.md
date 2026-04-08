# Cookie
This `Cookie` class allows you to manage cookies in your application.

## Method `Cookie::set($name, $value, $expires, $path, $domain, $secure, $httponly, $sameSite)`.
This static method adds a cookie before sending it to the browser with the specified parameters. Returns true if the cookie was set correctly, otherwise returns false.

**Parameters:**
- `$name` (string): The name of the cookie.
- `$value` (mixed): The value of the cookie.
- `$expires` (int): The expiration time of the cookie in seconds from the current time. Default is 0 (does not expire).
- `$path` (string): Path where the cookie will be available. Default is '/'.
- `$domain` (string): Domain to which the cookie is associated. Default is empty string.
- `$secure` (bool): Indicates if the cookie should only be sent through secure connections (HTTPS). Default is false.
- `$httponly` (bool): Indicates whether the cookie should only be accessible via HTTP (not JavaScript). Default is true.
- `$sameSite` (string): SameSite attribute - must be 'Strict', 'Lax', or 'None'. Default is 'Lax'.

```php
// Basic cookie
Cookie::set('helloworld', 'Hello World!');

// With options
Cookie::set('hellophp', 'Hello PHP!', 300, '/', 'localhost', true, true, 'Strict');

// SameSite=None requires secure=true (automatically enforced)
Cookie::set('crosssite', 'value', 0, '/', '', true, true, 'None');
```

## Method `Cookie::get(?string $name = null, mixed $default = null)`.
This static method gets the values of the cookies stored in `$_COOKIE`. It can get the value of a specific cookie, all cookies if no name is given, or the default value if the cookie does not exist.
```php
Cookie::get('helloworld');
// return "Hello World!"

Cookie::get();
/* return [
  "helloworld" => "Hello World!"
  "hellophp" => "Hello PHP!"
]*/ 

Cookie::get('unavailable','value');
// return "value"
```

## Method `Cookie::escaped(?string $name = null, mixed $default = null)`.
This static method retrieves cookie values HTML-escaped to prevent XSS attacks. Uses ENT_QUOTES | ENT_HTML5 for complete coverage.

**Parameters:**
- `$name` (string|null): The cookie name, or null for all cookies.
- `$default` (mixed): Default value if cookie doesn't exist.

```php
// Get single escaped cookie
Cookie::escaped('username');
// Returns HTML-escaped value

// Get all escaped cookies
Cookie::escaped();
/* Returns all $_COOKIE values with HTML entities escaped */
```
**Note:** Use this method when outputting cookie values in HTML to prevent XSS attacks.

## Method `Cookie::has(string $name)`.
This static method checks if a specific cookie exists in `$_COOKIE` and returns true otherwise it returns false.
```php
Cookie::has('helloworld');
// return true
```

## Method `Cookie::remove(string $name, string $path, string $domain, bool $secure, bool $httponly, string $sameSite)`.
This method deletes a specified cookie by setting its expiration to the past. Uses the same parameters as the original cookie for proper removal.

**Parameters:**
- `$name` (string): The cookie name to remove.
- `$path` (string): Path where the cookie was set. Default is '/'.
- `$domain` (string): Domain where the cookie was set. Default is empty.
- `$secure` (bool): Whether the cookie was secure. Default is false.
- `$httponly` (bool): Whether the cookie was HTTP-only. Default is true.
- `$sameSite` (string): SameSite attribute. Default is 'Lax'.

```php
Cookie::remove('helloworld');

// Remove with specific parameters (must match original cookie)
Cookie::remove('session', '/', 'example.com', true, true, 'Strict');
```