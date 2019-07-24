<?php declare(strict_types = 1);

namespace Shitwork\Exceptions;

class BadRequestException extends Exception implements HttpCodeContainer
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
