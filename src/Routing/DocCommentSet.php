<?php declare(strict_types=1);

namespace Shitwork\Routing;

final class DocCommentSet
{
    private $class = [];
    private $method = [];

    public function __construct(array $class = null, array $method = null)
    {
        foreach ($class ?? [] as $key => $value) {
            $this->class[\strtolower($key)] = $value;
        }

        foreach ($method ?? [] as $key => $value) {
            $this->method[\strtolower($key)] = $value;
        }
    }

    public function hasClassComment(string $name): bool
    {
        return \array_key_exists(\strtolower($name), $this->class);
    }

    public function getClassComment(string $name): ?string
    {
        return $this->class[\strtolower($name)] ?? null;
    }

    public function hasMethodComment(string $name): bool
    {
        return \array_key_exists(\strtolower($name), $this->method);
    }

    public function getMethodComment(string $name): ?string
    {
        return $this->method[\strtolower($name)] ?? null;
    }
}
