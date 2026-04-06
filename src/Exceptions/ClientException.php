<?php

namespace Mk4U\Http\Exceptions;

use Throwable;

class ClientException extends \Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        ?string $uri = null,
        ?string $method = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->uri = $uri;
        $this->method = $method;
    }

    protected ?string $uri = null;
    protected ?string $method = null;

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }
}