<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\InjectionException;
use Auryn\Injector;
use Shitwork\Routing\InvalidRouteException;
use Shitwork\Exceptions\NotFoundException;
use Shitwork\Routing\RouteTarget;

final class DynamicRoute extends Route
{
    private $object;
    private $className;
    private $varName;

    /**
     * @throws InvalidRouteException
     */
    public function __construct(string $httpMethod, string $uriPattern, $objectOrClassName, string $varName = 'method')
    {
        parent::__construct($httpMethod, $uriPattern);

        if (\is_object($objectOrClassName)) {
            $this->object = $objectOrClassName;
        } else if (\is_string($objectOrClassName)) {
            $this->className = $objectOrClassName;
        } else {
            throw new InvalidRouteException('Invalid', 'dynamic', 'Target must be a class name or object instance');
        }

        $this->varName = $varName;
    }

    /**
     * @throws NotFoundException
     * @throws \Shitwork\Routing\InvalidRouteException
     */
    public function getTarget(Injector $injector, array $vars): RouteTarget
    {
        $methodName = \strtr($vars[$this->varName], ['-' => '']);

        try {
            $object = $this->object ?? $injector->make($this->className);
        } catch (InjectionException $e) {
            throw new InvalidRouteException($this->className, $methodName, "Instance creation failed: {$e->getMessage()}", $e);
        }

        if (!\is_callable([$object, $methodName])) {
            throw new NotFoundException('Unknown endpoint: ' . $vars[$this->varName]);
        }

        return new RouteTarget([$object, $methodName], $vars, $object);
    }
}
