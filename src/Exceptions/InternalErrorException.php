<?php declare(strict_types = 1);

namespace Shitwork\Exceptions;

class InternalErrorException extends Exception implements HttpCodeContainer
{
    public function __construct($message, \Throwable $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
