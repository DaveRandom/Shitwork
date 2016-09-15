<?php declare(strict_types = 1);

namespace Shitwork;

class BadRequestException extends \RuntimeException
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
