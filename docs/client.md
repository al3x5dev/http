# Client
The `Client` class allows you to make HTTP requests using `cURL`.

## Usage

### Initialization
To create a new instance of the `Client` class, simply instantiate the class.

```php
require __DIR__ . '/vendor/autoload.php';

$client = new Mk4U\Http\Client();
```
You can pass an array of default options.

```php
require __DIR__ . '/vendor/autoload.php';

$options = [
    'timeout' => 100,
    'user_agent' => 'MyClient/1.0'
];

$client = new Mk4U\Http\Client($options);
```
#### List of configuration options:

- **max_redirects**: Maximum number of redirects to follow.
- **timeout**: Maximum time in seconds to wait for a response.
- **connect_timeout**: Maximum time in seconds to establish a connection.
- **http_version**: HTTP version to use (for example, CURL_HTTP_VERSION_1_1 or 1.1).
- **user_agent**: User agent to send in the request.
- **encoding**: Encoding to use for the response.
- **auto_referer**: Indicates if the Referer should be set automatically in redirections.
- **verbose**: Enable verbose mode. It will print information about the connection, requests, and responses to the standard output (stdout).Imprimirá información sobre la conexión, las solicitudes y las respuestas en la salida estándar (stdout).
- **verify**: Indicates if the SSL certificate should be verified.
- **cert**: Path to the SSL certificate file or directory.

### Sending Requests
The `Client` class allows you to send HTTP requests using different methods. You can use the `GET`, `POST`, `PUT`, `DELETE`, `HEAD`, `OPTIONS`, and `PATCH` methods.

```php
$response = $client->request('GET', 'https://api.example.com/resource');
```

### Handling Responses
The response from the request is returned as a [Response](https://github.com/alexsandrov16/http/blob/main/docs/response.md) object. You can access the content, status code, and headers of the response.

```php
$content = $response->getBody(); // Response content
$statusCode = $response->getStatusCode(); // HTTP status code
$headers = $response->getHeaders(); // Response headers
```

### Specific Methods
The `Client` class allows you to call specific methods for each type of HTTP request. For example:

```php
$response = $client->get('https://api.example.com/resource');
```

### Setting cURL Options
You can set additional options for `cURL` when sending the request. For example:

```php
$options = [
    'json' => ['key' => 'value'],
    'headers' => ['custom-header'=>'Hello World!']
];

$response = $client->post('https://api.example.com/resource', $options);
```

### Error Handling
If an error occurs during the request, an exception will be thrown. You can handle this using a try-catch block.

```php
try {
    $response = $client->get('https://api.example.com/resource');
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Closing the Session
The `cURL` session is automatically closed when the instance of the `Client` class is destroyed.

## Examples

### Basic Usage
```php
require __DIR__ . '/vendor/autoload.php';

$options = [
    'timeout' => 10,
    'user_agent' => 'MyClient/1.0',
    'headers' => ['Authorization' => 'Bearer your_token_here']
];

$client = new Mk4U\Http\Client($options);

try {
    // Send GET request
    $response = $client->get('https://jsonplaceholder.typicode.com/posts/1');

    // Get response
    if ($response->getStatusCode() === 200) {
        $content = json_decode($response->getBody());
        echo "Response Content: " . $content;
    } else {
        echo "Error: " . $response->getStatusCode();
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Sending form data (application/x-www-form-urlencoded)
```php
$options = [
    'form_params' => [
        'title'=> 'foo',
        'body'=> 'bar',
        'userId'=> 1,
    ],
    'headers'=>[
        'custom-header'=>'My Custom Header'
    ]
];

try {
    $response = $client->post('https://jsonplaceholder.typicode.com/posts', $options);

    if ($response->getStatusCode() === 201) {
        echo "Resource created successfully!";
        print_r($response->getBody());
    } else {
        echo "Error: " . $response->getStatusCode();
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Sending multipart data
```php
$options = [
    'multipart' => [
        [
            'name' => 'title',
            'contents' => 'bar'
        ],
        [
            'name' => 'body',
            'contents' => 'baz'
        ],
        [
            'name' => 'userId',
            'contents' => 2
        ],
        [
            'name' => 'file',
            'contents' => __DIR__.'/filename.ext'
        ]
    ],
    'headers'=>[
        'custom-header'=>'My Custom Header'
    ]
];

try {
    $response = $client->post('https://jsonplaceholder.typicode.com/posts', $options);

    if ($response->getStatusCode() === 201) {
        echo "Resource created successfully!";
        print_r($response->getBody());
    } else {
        echo "Error: " . $response->getStatusCode();
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

### Sending Json data (application/json)
```php
$options = [
    'json' => [
        'title'=> 'foobar',
        'body'=> 'barbaz',
        'userId'=> 3,
    ],
    'headers'=>[
        'custom-header'=>'My Custom Header'
    ]
];

try {
    $response = $client->post('https://jsonplaceholder.typicode.com/posts', $options);

    if ($response->getStatusCode() === 201) {
        echo "Resource created successfully!";
        print_r($response->getBody());
    } else {
        echo "Error: " . $response->getStatusCode();
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```