<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;
use Shitwork\Routing\Router;
use Shitwork\Templating\TemplateFetcher;

function injector(Injector $injector = null): Injector
{
    static $persistent;

    if (isset($persistent)) {
        return $persistent;
    }

    $persistent = $injector ?? new Injector;

    return $persistent->share($persistent); // yolo
}

function bootstrap(Injector $injector = null): Injector
{
    static $done = false;

    $injector = \Shitwork\injector($injector);

    return $done ? $injector : $done = $injector
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
    try {
        $code = $e->getCode();
        $message = HttpStatus::getMessage($e->getCode());
    } catch (\LogicException $e) {
        $code = HttpStatus::INTERNAL_SERVER_ERROR;
        $message = HttpStatus::getMessage($code);
    }

    \header(\sprintf(
        '%s/%s %d %s',
        isset($request) ? $request->getProtocolName() : 'HTTP',
        isset($request) ? $request->getProtocolVersion() : '1.1',
        $code, $message
    ));
}

function error_log_dump(...$vars)
{
    \ob_start();
    var_dump(...$vars);
    \error_log(\ob_get_clean());
}
