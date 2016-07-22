<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;

function bootstrap(): Injector
{
    $injector = new Injector();
    $injector->share($injector); // yolo

    $injector->share(Request::class);
    $injector->share(Router::class);
    $injector->share(ScriptCollection::class);
    $injector->share(Session::class);
    $injector->share(StyleCollection::class);
    $injector->share(TemplateFetcher::class);

    return $injector;
}
