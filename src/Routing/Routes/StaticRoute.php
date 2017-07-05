<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\Injector;
use Shitwork\Routing\Exceptions\InvalidRouteException;
use Shitwork\Routing\RouteTarget;

class StaticRoute extends Route
{
    private $className;
    private $methodName;

    public function __construct(string $httpMethod, string $uriPattern, string $className, string $methodName)
    {
        parent::__construct($httpMethod, $uriPattern);

        $this->className = $className;
        $this->methodName = $methodName;
    }

    public function getTarget(Injector $injector, array $vars): RouteTarget
    {
        $object = $injector->make($this->className);
        $target = [$object, $this->methodName];

        if (!is_callable($target)) {
            throw new InvalidRouteException;
        }

        return new RouteTarget($target, $vars, $object);
    }
}
