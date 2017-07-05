<?php declare(strict_types = 1);

namespace Shitwork\Routing\Exceptions;

use Shitwork\Exceptions\InternalErrorException;

class InvalidRouteException extends InternalErrorException
{
    public function __construct($message = 'Invalid route target', $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
