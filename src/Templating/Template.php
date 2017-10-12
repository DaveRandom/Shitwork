<?php declare(strict_types=1);

namespace Shitwork\Templating;

interface Template
{
    function renderString(array $variables): string;
    function renderOutput(array $variables): void;
}
