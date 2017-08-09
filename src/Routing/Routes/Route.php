<?php declare(strict_types = 1);

namespace Shitwork\Routing\Routes;

use Auryn\Injector;
use Shitwork\Routing\DocCommentSet;
use Shitwork\Routing\RouteTarget;

abstract class Route
{
    private $httpMethod;
    private $uriPattern;

    private function parseDocComment(string $comment): array
    {
        $result = [];

        foreach (\preg_split('#[\r\n]+#', $comment, -1, \PREG_SPLIT_NO_EMPTY) as $line) {
            if (\preg_match('#\s\*\s*@([a-z0-9\-_]+)\s*(.*)#i', $line, $match)) {
                $result[\strtolower($match[1])] = $match[2] ?? '';
            }
        }

        return $result;
    }

    protected function getDocComments($object, string $methodName): DocCommentSet
    {
        $classReflection = new \ReflectionClass($object);
        $methodReflection = $classReflection->getMethod($methodName);

        $classComment = false !== ($comment = $classReflection->getDocComment())
            ? $this->parseDocComment($comment)
            : null;

        $methodComment = false !== ($comment = $methodReflection->getDocComment())
            ? $this->parseDocComment($comment)
            : null;

        return new DocCommentSet($classComment, $methodComment);
    }

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
