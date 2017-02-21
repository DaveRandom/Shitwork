<?php declare(strict_types = 1);

namespace Shitwork;

use Shitwork\Exceptions\InvalidTemplateException;

class Template
{
    const NO_BEFORE = 0b01;
    const NO_AFTER  = 0b10;
    const PATH_ONLY = self::NO_BEFORE | self::NO_AFTER;

    private $path;
    private $vars;
    private $before;
    private $after;

    public function __construct(string $path, array $before = [], array $after = [], array $vars = [])
    {
        if (!is_file($path)) {
            throw new InvalidTemplateException('Cannot render template ' . $path . ': file not found');
        } else if (!is_readable($path)) {
            throw new InvalidTemplateException('Cannot render template ' . $path . ': file not readable');
        }

        $this->path = $path;
        $this->vars = $vars;
        $this->before = $before;
        $this->after = $after;
    }

    private static function require()
    {
        extract(func_get_arg(1));

        /** @noinspection PhpIncludeInspection */
        require func_get_arg(0);
    }

    public function render(array $vars = null, int $flags = 0)
    {
        $vars = $vars
            ? array_merge($this->vars, $vars)
            : $this->vars;

        if (!($flags & self::NO_BEFORE)) {
            foreach ($this->before as $before) {
                self::require($before, $vars);
            }
        }

        self::require($this->path, $vars);

        if (!($flags & self::NO_AFTER)) {
            foreach ($this->after as $after) {
                self::require($after, $vars);
            }
        }
    }
}
