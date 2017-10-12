<?php declare(strict_types = 1);

namespace Shitwork;

final class ScriptCollection implements \IteratorAggregate
{
    private $scripts = [];

    public function add($data)
    {
        $this->scripts[] = \preg_match('/\s+/', $data)
            ? ['body' => \trim($data)]
            : ['src' => $data];
    }

    public function get()
    {
        return $this->scripts;
    }

    public function clear()
    {
        $this->scripts = [];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->scripts);
    }
}
