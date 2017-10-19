<?php declare(strict_types=1);

namespace Shitwork\Templating;

use Shitwork\Exceptions\UndefinedTemplateVariableException;

final class StringTemplate implements Template
{
    private const REGEX = '/\\\\.|{([a-z0-9_.\-]+)}/iu';
    private const ESCAPE_SEQUENCES = [
        'e' => "\e", 'f' => "\f", 'n' => "\n",
        'r' => "\r", 't' => "\t", 'v' => "\v",
        '{' => '{', '\\' => '\\',
    ];

    public const ERR_IGNORE = 0;
    public const ERR_EMPTY = 1;
    public const ERR_THROW = 2;

    private $templateString;
    private $errorMode;

    public function __construct(string $templateString, int $errorMode = self::ERR_IGNORE)
    {
        $this->templateString = $templateString;

        $this->setErrorMode($errorMode);
    }

    public function getTemplateString(): string
    {
        return $this->templateString;
    }

    public function getErrorMode(): int
    {
        return $this->errorMode;
    }

    public function setErrorMode(int $errorMode): void
    {
        if (!\in_array($errorMode, [self::ERR_IGNORE, self::ERR_EMPTY, self::ERR_THROW], true)) {
            throw new \LogicException("Unknown error mode: {$errorMode}");
        }

        $this->errorMode = $errorMode;
    }

    public function renderString(array $variables): string
    {
        return \preg_replace_callback(self::REGEX, function(array $match) use($variables): string {
            if ($match[0][0] === '\\') {
                return self::ESCAPE_SEQUENCES[$match[0][1]] ?? $match[0];
            }

            if (\array_key_exists($match[1], $variables)) {
                return (string)$variables[$match[1]];
            }

            switch ($this->errorMode) {
                case self::ERR_EMPTY:
                    return '';

                case self::ERR_THROW:
                    throw new UndefinedTemplateVariableException("Variable '{$match[1]}' not defined");

                case self::ERR_IGNORE:
                default:
                    return $match[0];
            }
        }, $this->templateString);
    }

    public function renderOutput(array $variables): void
    {
        echo $this->renderString($variables);
    }
}
