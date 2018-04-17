<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\Injector;
use Shitwork\DocComment;
use Shitwork\Exceptions\LogicError;
use Shitwork\Exceptions\NotFoundException;
use Shitwork\Routing\DocCommentSet;
use Shitwork\Routing\InvalidRouteException;
use Shitwork\Routing\RouteTarget;

abstract class Route
{
    private $httpMethod;
    private $uriPattern;

    /**
     * @throws \ReflectionException
     */
    protected function getDocComments($object, string $methodName): DocCommentSet
    {
        $classReflection = new \ReflectionClass($object);
        $methodReflection = $classReflection->getMethod($methodName);

        $classComment = false !== ($comment = $classReflection->getDocComment())
            ? DocComment::parse($comment)
            : null;

        $methodComment = false !== ($comment = $methodReflection->getDocComment())
            ? DocComment::parse($comment)
            : null;

        return new DocCommentSet($classComment, $methodComment);
    }

    /**
     * @param array|callable $target
     * @return StaticRoute
     * @throws InvalidRouteException
     * @throws LogicError
     */
    public static function static(string $httpMethod, string $uriPattern, array $target): StaticRoute
    {
        if (!\is_string($target[0] ?? null) || !\is_string($target[1] ?? null) || !\method_exists($target[0], $target[1])) {
            throw new LogicError("Target must be a callable method reference");
        }

        return new StaticRoute($httpMethod, $uriPattern, $target[0], $target[1]);
    }

    /**
     * @throws InvalidRouteException
     */
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

    /**
     * @throws InvalidRouteException
     * @throws NotFoundException
     */
    public abstract function getTarget(Injector $injector, array $vars): RouteTarget;
}
