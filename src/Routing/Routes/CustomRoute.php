<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\Injector;
use Shitwork\Routing\RouteTarget;

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
