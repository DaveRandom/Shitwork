<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;
use Shitwork\Routing\Router;

const HTTP_ERROR_CODES = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    500 => 'Internal Server Error',
];

function injector(Injector $injector = null): Injector
{
    static $persistent;

    if (isset($persistent)) {
        return $persistent;
    }

    return $persistent = $injector ?? new Injector();
}

function bootstrap(Injector $injector = null): Injector
{
    static $done = false;

    $injector = \Shitwork\injector($injector);

    return $done ? $injector : $done = $injector
        ->share($injector) // yolo
        ->share(Request::class)
        ->share(Router::class)
        ->share(ScriptCollection::class)
        ->share(Session::class)
        ->share(StyleCollection::class)
        ->share(TemplateFetcher::class)
    ;
}

function h($raw, int $flags = ENT_COMPAT | ENT_HTML5): string
{
    return \htmlspecialchars(\trim((string)$raw), $flags | ENT_COMPAT, 'utf-8');
}

function parse_bool($var)
{
    return \is_string($var)
        ? !\preg_match('/^0|no|false|off$/i', $var)
        : (bool)$var;
}

function http_response_line_from_exception(\Throwable $e, Request $request = null)
{
    $statusCode = \array_key_exists($e->getCode(), HTTP_ERROR_CODES)
        ? $e->getCode()
        : 500;

    \header(\sprintf(
        '%s/%s %d %s',
        isset($request) ? $request->getProtocolName() : 'HTTP',
        isset($request) ? $request->getProtocolVersion() : '1.1',
        $statusCode, HTTP_ERROR_CODES[$statusCode]
    ));
}
