<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\Injector;
use Shitwork\Routing\Exceptions\InvalidRouteException;
use Shitwork\Routing\RouteTarget;

final class StaticRoute extends Route
{
    private $className;
    private $methodName;
    private $docComments;

    public function __construct(string $httpMethod, string $uriPattern, string $className, string $methodName)
    {
        parent::__construct($httpMethod, $uriPattern);

        $this->className = $className;
        $this->methodName = $methodName;
        $this->docComments = $this->getDocComments($className, $methodName);
    }

    public function getTarget(Injector $injector, array $vars): RouteTarget
    {
        $object = $injector->make($this->className);
        $target = [$object, $this->methodName];

        if (!is_callable($target)) {
            throw new InvalidRouteException;
        }

        return new RouteTarget($target, $vars, $object, $this->docComments);
    }
}
