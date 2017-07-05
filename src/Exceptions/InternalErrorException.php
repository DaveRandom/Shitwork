<?php declare(strict_types = 1);

namespace Shitwork\Exceptions;

class InternalErrorException extends Exception
{
    public function __construct($message, $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
