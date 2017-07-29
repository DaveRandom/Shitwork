<?php declare(strict_types = 1);

namespace Shitwork\Routing;

class RouteTarget
{
    private $callable;
    private $vars;
    private $object;

    public function __construct(callable $callable, array $vars, $object)
    {
        $this->callable = $callable;
        $this->vars = $vars;
        $this->object = $object;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function dispatch(...$vars)
    {
        $vars[] = $this->vars;

        return call_user_func($this->callable, ...$vars);
    }
}
