<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\InjectionException;
use Auryn\Injector;
use Shitwork\Routing\InvalidRouteException;
use Shitwork\Routing\RouteTarget;

final class StaticRoute extends Route
{
    private $className;
    private $methodName;
    private $docComments;

    /**
     * @throws \Shitwork\Routing\InvalidRouteException
     */
    public function __construct(string $httpMethod, string $uriPattern, string $className, string $methodName)
    {
        parent::__construct($httpMethod, $uriPattern);

        $this->className = $className;
        $this->methodName = $methodName;

        try {
            $this->docComments = $this->getDocComments($className, $methodName);
        } catch (\ReflectionException $e) {
            throw new InvalidRouteException($className, $methodName, "Cannot reflect class {$className}", $e);
        }
    }

    /**
     * @throws \Shitwork\Routing\InvalidRouteException
     */
    public function getTarget(Injector $injector, array $vars): RouteTarget
    {
        try {
            $object = $injector->make($this->className);
        } catch (InjectionException $e) {
            throw new InvalidRouteException($this->className, $this->methodName, "Instance creation failed: {$e->getMessage()}", $e);
        }

        $target = [$object, $this->methodName];

        if (!\is_callable($target)) {
            throw new InvalidRouteException($this->className, $this->methodName, 'Method reference is not callable');
        }

        return new RouteTarget($target, $vars, $object, $this->docComments);
    }
}
