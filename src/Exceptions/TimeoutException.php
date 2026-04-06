<?php

namespace Mk4U\Http\Exceptions;

use Throwable;

class TimeoutException extends ClientException
{
    public function __construct(
        string $message = '',
        ?Throwable $previous = null,
        ?string $uri = null,
        ?string $method = null
    ) {
        parent::__construct($message, 0, $previous, $uri, $method);
    }
}