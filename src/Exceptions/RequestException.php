<?php

namespace Mk4U\Http\Exceptions;

use Mk4U\Http\Response;
use Throwable;

class RequestException extends ClientException
{
    public function __construct(
        string $message = '',
        ?Throwable $previous = null,
        ?string $uri = null,
        ?string $method = null,
        ?Response $response = null
    ) {
        parent::__construct($message, 0, $previous, $uri, $method);
        
        $this->response = $response;
    }

    protected ?Response $response = null;

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }
}