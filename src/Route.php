<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;

abstract class Route
{
    private $httpMethod;
    private $uriPattern;

    public static function static(string $httpMethod, string $uriPattern, string $className, string $methodName): StaticRoute
    {
        return new StaticRoute($httpMethod, $uriPattern, $className, $methodName);
    }

    public static function dynamic(string $httpMethod, string $uriPattern, $objectOrClassName, string $varName = 'method')
    {
        return new DynamicRoute($httpMethod, $uriPattern, $objectOrClassName, $varName);
    }

    public static function custom(string $httpMethod, string $uriPattern, callable $callback)
    {
        return new CustomRoute($httpMethod, $uriPattern, $callback);
    }

    protected function __construct(string $httpMethod, string $uriPattern)
    {
        $this->httpMethod = $httpMethod;
        $this->uriPattern = $uriPattern;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function getUriPattern(): string
    {
        return $this->uriPattern;
    }

    public abstract function getTarget(Injector $injector, array $vars): RouteTarget;
}
