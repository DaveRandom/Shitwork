<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\Injector;
use Shitwork\Routing\Exceptions\InvalidRouteException;
use Shitwork\Routing\RouteTarget;

class DynamicRoute extends Route
{
    private $object;
    private $className;
    private $varName;

    public function __construct(string $httpMethod, string $uriPattern, $objectOrClassName, string $varName = 'method')
    {
        parent::__construct($httpMethod, $uriPattern);

        if (is_object($objectOrClassName)) {
            $this->object = $objectOrClassName;
        } else {
            $this->className = (string)$objectOrClassName;
        }

        $this->varName = $varName;
    }

    public function getTarget(Injector $injector, array $vars): RouteTarget
    {
        $object = $this->object ?? $injector->make($this->className);
        $target = [$object, strtr($vars[$this->varName], ['-' => ''])];

        if (!is_callable($target)) {
            throw new InvalidRouteException('Unknown endpoint: ' . $vars[$this->varName]);
        }

        return new RouteTarget($target, $vars, $object);
    }
}
