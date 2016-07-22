<?php declare(strict_types = 1);

namespace Shitwork;

class TemplateFetcher
{
    private $path;
    private $vars;
    private $before;
    private $after;

    public function __construct(string $path, array $before = [], array $after = [], array $vars = [])
    {
        $this->path = $path;
        $this->before = array_map([$this, 'makePath'], $before);
        $this->after = array_map([$this, 'makePath'], $after);
        $this->vars = $vars;
    }

    public function makePath(string $name): string
    {
        return sprintf($this->path, $name);
    }

    public function fetch(string $name): Template
    {
        return new Template(sprintf($this->path, $name), $this->before, $this->after, $this->vars);
    }
}
