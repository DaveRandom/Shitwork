<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;

function injector(Injector $injector = null): Injector
{
    static $persistent;

    if (isset($injector)) {
        $persistent = $injector;
        $persistent->share($persistent);
    }

    if (!isset($persistent)) {
        $persistent = new Injector();
        $persistent->share($persistent);
    }

    return $persistent ?? $persistent = new Injector();
}

function bootstrap(Injector $injector = null): Injector
{
    $injector = injector($injector);
    $injector->share($injector); // yolo

    $injector->share(Request::class);
    $injector->share(Router::class);
    $injector->share(ScriptCollection::class);
    $injector->share(Session::class);
    $injector->share(StyleCollection::class);
    $injector->share(TemplateFetcher::class);

    return $injector;
}

function h($raw, int $flags = ENT_COMPAT): string
{
    return htmlspecialchars(trim((string)$raw), $flags | ENT_COMPAT, 'utf-8');
}

function parse_bool($var)
{
    return is_string($var)
        ? !preg_match('/^0|no|false|off$/i', $var)
        : (bool)$var;
}
