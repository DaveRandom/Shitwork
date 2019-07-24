<?php declare(strict_types=1);

namespace Shitwork\Collections;

use Shitwork\Exceptions\LogicError;

trait ImmutableArrayAccess
{
    public function offsetSet(/** @noinspection PhpUnusedParameterInspection */ $offset, $value)
    {
        throw new LogicError(static::class . " is immutable");
    }

    public function offsetUnset(/** @noinspection PhpUnusedParameterInspection */ $offset)
    {
        throw new LogicError(static::class . " is immutable");
    }
}
