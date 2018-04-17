<?php declare(strict_types = 1);

namespace Shitwork\Templating;

use Shitwork\Exceptions\InvalidTemplateException;

final class FileTemplate implements Template
{
    const NO_BEFORE = 0b01;
    const NO_AFTER  = 0b10;
    const PATH_ONLY = self::NO_BEFORE | self::NO_AFTER;

    private $path;
    private $variables;
    private $before;
    private $after;

    /**
     * @throws InvalidTemplateException
     */
    public function __construct(string $path, array $before = [], array $after = [], array $variables = [])
    {
        if (!\is_file($path)) {
            throw new InvalidTemplateException('Cannot render template ' . $path . ': file not found');
        }

        if (!\is_readable($path)) {
            throw new InvalidTemplateException('Cannot render template ' . $path . ': file not readable');
        }

        $this->path = $path;
        $this->variables = $variables;
        $this->before = $before;
        $this->after = $after;
    }

    private static function require(): void
    {
        \extract(\func_get_arg(1));

        /** @noinspection PhpIncludeInspection */
        require \func_get_arg(0);
    }

    public function renderString(array $variables = null, int $flags = 0): string
    {
        \ob_start();
        $this->renderOutput($variables, $flags);
        return \ob_get_clean();
    }

    public function renderOutput(array $variables = null, int $flags = 0): void
    {
        $variables = $variables
            ? \array_merge($this->variables, $variables)
            : $this->variables;

        if (!($flags & self::NO_BEFORE)) {
            foreach ($this->before as $before) {
                self::require($before, $variables);
            }
        }

        self::require($this->path, $variables);

        if (!($flags & self::NO_AFTER)) {
            foreach ($this->after as $after) {
                self::require($after, $variables);
            }
        }
    }
}
