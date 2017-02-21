<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;
use Shitwork\Exceptions\InvalidRouteException;

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
            throw new InvalidRouteException('Invalid route target');
        }

        return new RouteTarget($target, $vars, $object);
    }
}
