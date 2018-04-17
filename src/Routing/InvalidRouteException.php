<?php declare(strict_types = 1);

namespace Shitwork\Routing;

use Shitwork\Exceptions\InternalErrorException;

class InvalidRouteException extends InternalErrorException
{
    public function __construct($class, string $methodName, string $message, \Throwable $previous = null)
    {
        $className = \is_object($class)
            ? \get_class($class)
            : (string)$class;
        $message = \trim($message);

        parent::__construct("Invalid route target {$className}::{$methodName}(): {$message}", $previous);
    }
}
