<?php declare(strict_types = 1);

namespace Shitwork;

class StyleCollection
{
    private $styles = [];

    public function add($data)
    {
        $this->styles[] = \preg_match('/\s+/', $data)
            ? ['body' => \trim($data)]
            : ['href' => $data];
    }

    public function get()
    {
        return $this->styles;
    }

    public function clear()
    {
        $this->styles = [];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->styles);
    }
}
