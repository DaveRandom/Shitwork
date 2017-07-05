<?php declare(strict_types = 1);

namespace Shitwork\Exceptions;

class MethodNotAllowedException extends Exception
{
    public function __construct($message, $code = 405, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
