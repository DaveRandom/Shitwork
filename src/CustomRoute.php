<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;

class CustomRoute extends Route
{
    private $callback;

    public function __construct(string $httpMethod, string $uriPattern, callable $callback)
    {
        parent::__construct($httpMethod, $uriPattern);

        $this->callback = $callback;
    }

    public function getTarget(Injector $injector, array $vars): RouteTarget
    {
        return new RouteTarget($this->callback, $vars, null);
    }
}
