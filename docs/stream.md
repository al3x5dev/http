# Stream
The `Stream` class allows you to interact with data streams in PHP, providing a simple interface for reading and writing data to stream resources.


> [!NOTE] 
> When working with streams, it is important to keep in mind that read and write operations 
> can be more efficient than loading all content into memory, especially with large files. 
> Using methods such as read and write allows for more controlled handling of data.
> 
> For more information on PHP's stream handling, you can refer to
> the [official PHP documentation](https://www.php.net/manual/book.stream.php) on Streams.


## Usage

### Creating a new Stream object
To create a new instance of the Stream class, you must provide a stream resource or a string representing the path to a file, along with the opening mode of the stream.

```php
require __DIR__ . '/vendor/autoload.php';

$stream = new Mk4U\Http\Stream('path/to/file.txt', 'r+');
```

The available opening modes are:
- `r` Read
- `rb` Read in binary mode
- `rt` Read in text mode
- `r+` Read and write
- `rb+` Read and write in binary mode
- `rt+` Read and write in text mode
- `w` Write (truncate)
- `wb` Write in binary mode (truncate)
- `wt` Write in text mode (truncate)
- `w+` Write and read (truncate)
- `wb+` Write and read in binary mode (truncate)
- `a` Write (append)
- `ab` Write (append) in binary mode
- `at` Write (append) in text mode
- `a+` Write (append) and read
- `ab+` Write (append) and read in binary mode
- `x` Create and write (fails if exists)
- `xb` Create and write in binary mode (fails if exists)
- `x+` Create, write and read (fails if exists)
- `xb+` Create, write and read in binary mode (fails if exists)
- `c` Write (no truncate, no create)
- `cb` Write in binary mode (no truncate, no create)
- `c+` Write and read (no truncate, no create)
- `cb+` Write and read in binary mode (no truncate, no create)

### Returns object converted to a string
The `__toString` magic method returns the strean converted to a string.
```php
echo $stream; // returns the contents of the stream
```


### Method `Stream::close()`
Closes the flow and releases any underlying resource.
```php
$stream->close();
```

### Method `Stream::detach()`
Separates the underlying resource from the flow and returns it.
```php
$resource = $stream->detach();
```

### Method `Stream::getSize()`
Gets the size of the stream, if known.
```php
$size = $stream->getSize(); // returns the size in bytes
```

### Method `Stream::tell()`
Returns the current position of the file read/write pointer.
```php
$position = $stream->tell(); // returns the current position
```

### Method `Stream::eof()`
Returns true if the pointer is at the end of the stream.
```php
$isEnd = $stream->eof(); // returns true if at the end
```

### Method `Stream::isSeekable()`
Returns whether the stream is searchable or not.
```php
$isSeekable = $stream->isSeekable(); // returns true or false
```

### Method `Stream::seek(int $offset, int $whence = SEEK_SET)`
Searches for a position in the stream.
```php
$stream->seek(0); // return to the beginning of the flow
```

### Method `Stream::rewind()`
Returns to the beginning of the stream.
```php
$stream->rewind();
```

### Method `Stream::isWritable()`
Returns whether the stream is writable.
```php
$isWritable = $stream->isWritable(); // returns true or false
```

### Method `Stream::write(string $data)`
Writes data to the stream.
```php
$bytesWritten = $stream->write("Nuevo contenido"); // returns the number of bytes written
```

### Method `Stream::isReadable()`
Returns whether the stream is readable or not.
```php
$isReadable = $stream->isReadable(); // returns true or false
```

### Method `Stream::read(int $length)`
Reads data from the stream.
```php
$data = $stream->read(10); // reads up to 10 bytes
```

### Method `Stream::getContents()`
Returns the remaining content in a string.
```php
$contents = $stream->getContents(); // returns the remaining contents
```

### Method `Stream::getMetadata(?string $key = null)`
Gets metadata from the stream.
```php
$metadata = $stream->getMetadata(); // returns all metadata

$mode = $stream->getMetadata('mode'); // returns the flow mode
```

## Examples

### Example 1: Read a file

```php
try {
    $stream = new Mk4U\Http\Stream('path/to/file.txt', 'r');
    echo $stream->getContents();
    $stream->close();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Example 2: Writing to file

```php
try {
    $stream = new Mk4U\Http\Stream('path/to/file.txt', 'a+');
    $stream->write("Hello World\n");
    $stream->close();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Example 3: Using search methods

```php
try {
    $stream = new Mk4U\Http\Stream('path/to/file.txt', 'r+');

    // go to the beginning of the file
    $stream->seek(0); 

    // read the first 10 bytes
    echo $stream->read(10); 

     // go back to the beginning of the file
    $stream->rewind();
    
    // read the entire contents of the file
    $content = $stream->read($stream->getSize());
    echo $content;
    
    // close the stream
    $stream->close();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```