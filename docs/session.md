# Session
This `Session` class allows you to manage sessions in your application.

## Method `Session::start()`.
This static method is responsible for initializing the session. 

By default this method initializes the session by setting the following options:
- "name": "mk4u"
- "use_cookies": true,
- "use_only_cookies": true
- "cookie_lifetime": 0
- "cookie_httponly": true
- "cookie_secure": true
- "use_strict_mode": true 

But you can change these values to suit your use case; [read more 👀](https://www.php.net/manual/es/session.configuration.php)

Returns true if the session is successfully started

```php
Session::start();
```

## Method `Session::set(string $name, mixed $value)`.
This static method is responsible for setting values for `$_SESSION`. If the session variable already exists, this method overwrites its value with the new value provided. This method does not return any value, as it simply sets the value of the session variable to `$_SESSION`. You can call this method at any time without having initialized the session, we do it for you.

**Parameters**
- $name (string): The name of the session variable to be set.
- $value (mixed): The value to assign to the session variable.

```php
Session::set('hello', 'Hello World!');
```

## Method `Session::get(?string $name = null, mixed $default = null)`.
This static method retrieves the values stored in $_SESSION that match the name passed. It may return the value of the superglobal $_SESSION if a value for name is not provided. If the session name does not exist, this method returns the value of $default.

**Parameters:**
- `$name` (string|null): The name of the session variable to retrieve. Default is `null`.
- `$default` (mixed): The default value to return if the session variable is not set. Default is `null`.

```php
Session::get('hello')
// return "Hello World!"

Session::get();
/* return [
  "hello" => "Hello World!"
]*/ 

Session::get('unavailable','value');
// return "value"

Session::get('unavailable');
//return null 
```

## Method `Session::has(string $name)`.
This static method checks if a specific session exists in `$_SESSION` and returns true otherwise it returns false.
```php
Session::has('hello');
// return true
```

## Method `Session::remove(string $name)`.
This method deletes a specified session.

```php
Session::remove('hello');
```

## Method `Session::id()`.
This method returns the current session ID.

```php
Session::id();
// "8r050i4f7mdcc3sikc2mill7ck"
```

## Method `Session::renewId()`.
This method generates a new session ID.

```php
Session::renewId();
Session::id();
// "g0d22ie4agrc7fheackc2hsdbc"
```

## Method `Session::destroy()`.
This method deletes all session variables and destroys the session.

```php
Session::destroy();
```

## Method `Session::delete(string $name)`.
Alias to remove.

```php
Session::delete('hello');
```

## Method `Session::flash(string $name, mixed $value = null)`.
This method sets a flash message in the session cookie, the data stored in the session using this method will be available immediately and during the subsequent HTTP request. After the subsequent HTTP request, the detailed data will be deleted.

**Parameters:**
- `$name` (string): The name of the message identifier to set.
- `$value` (mixed): The value to assign to the flash message. Default is `null`.

### Set a session message
To store a flash message just call the `Session::flash()` method and pass the name and the message.

```php
Session::flash('message','Hello Word!!');
```

### Return a session message
To return the message just call the `Session::flash()` method but this time just provide the name.

> [!NOTE]
> Remember that `Session::flash()` will only return the flash message once after that it is deleted.

```php
echo Session::flash('message');
// Hello Word!!
```

## CSRF Protection

The Session class provides built-in CSRF (Cross-Site Request Forgery) protection methods.

### Method `Session::csrfToken()`.
Generates and returns a CSRF token. The token is stored in the session and regenerated if it doesn't exist.

```php
Session::start();
$token = Session::csrfToken();
// Returns a 64-character hex string
```

### Method `Session::csrfField()`.
Returns an HTML hidden input field containing the CSRF token. This can be directly added to forms.

```php
Session::start();
echo Session::csrfField();
// Output: <input type="hidden" name="_token" value="...">
```

**Usage in a form:**
```php
<form method="POST" action="/submit">
    <?= Session::csrfField() ?>
    <input type="text" name="data">
    <button type="submit">Submit</button>
</form>
```

### Method `Session::validateCsrf(string $token)`.
Validates a CSRF token against the stored session token. Uses constant-time comparison to prevent timing attacks.

**Parameters:**
- `$token` (string): The token to validate.

```php
Session::start();
$isValid = Session::validateCsrf($_POST['_token']);
// Returns true if valid, false otherwise
```

### Method `Session::validateRequestCsrf()`.
Automatically validates the CSRF token from the current request. Checks both POST data and X-CSRF-Token header.

```php
Session::start();
if (Session::validateRequestCsrf()) {
    // Token is valid, process the request
} else {
    // Invalid token, reject the request
}
```

**Complete example:**
```php
// In your form handler
Session::start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateRequestCsrf()) {
        Response::plain('Invalid CSRF token', Status::Forbidden)->send();
        exit;
    }
    
    // Process form data...
    $name = $_POST['name'];
}
```