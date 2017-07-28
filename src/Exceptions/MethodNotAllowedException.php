<?php declare(strict_types = 1);

namespace Shitwork\Exceptions;

class MethodNotAllowedException extends Exception
{
    public function __construct($message, \Throwable $previous = null)
    {
        parent::__construct($message, 405, $previous);
    }
}