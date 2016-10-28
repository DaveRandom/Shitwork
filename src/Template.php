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

    private $currentRequirePath;

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

    private function require($path, $vars)
    {
        $this->currentRequirePath = $path;
        extract($vars);

        if (!isset($vars['path'])) {
            unset($path);
        }
        if (!isset($vars['vars'])) {
            unset($vars);
        }

        /** @noinspection PhpIncludeInspection */
        require $this->currentRequirePath;
    }

    public function render(array $vars = null, int $flags = 0)
    {
        if ($vars) {
            $vars = array_merge($this->vars, $vars);
        }

        if (!($flags & self::NO_BEFORE)) {
            foreach ($this->before as $before) {
                $this->require($before, $vars);
            }
        }

        $this->require($this->path, $vars);

        if (!($flags & self::NO_AFTER)) {
            foreach ($this->after as $after) {
                $this->require($after, $vars);
            }
        }
    }
}
