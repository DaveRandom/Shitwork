<?php declare(strict_types=1);

namespace Shitwork\Exceptions;

interface HttpCodeContainer extends \Throwable
{
    function getCode();
}
