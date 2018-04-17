<?php declare(strict_types=1);

namespace Shitwork;

final class DocComment
{
    private $tags;

    public static function parse(string $comment): self
    {
        $result = [];

        foreach (\preg_split('#[\r\n]+#', $comment, -1, \PREG_SPLIT_NO_EMPTY) as $line) {
            if (!\preg_match('#\s\*\s*@([a-z][a-z0-9\-_]*)[ \t]*(.*)#i', $line, $match)) {
                continue;
            }

            $name = \strtolower($match[1]);
            $value = \trim($match[2]);

            $result[$name] = $result[$name] ?? [];

            if ($value !== '') {
                $result[$name][] = $value;
            }
        }

        return new self($result);
    }

    private function getValuesByName(string $name): ?array
    {
        return $this->tags[\strtolower($name)] ?? null;
    }

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function hasTag(string $name): bool
    {
        return $this->getValuesByName($name) !== null;
    }

    public function hasFlag(string $name, bool $default = false): bool
    {
        // Tag not defined, return the default
        if (null === $values = $this->getValuesByName($name)) {
            return $default;
        }

        // Tag defined with no value, consider to be "on"
        if (!isset($values[0])) {
            return true;
        }

        return !\in_array(\strtolower($values[0]), ['no', 'off', 'false']);
    }

    public function hasValues(string $name): bool
    {
        if (null === $values = $this->getValuesByName($name)) {
            return false;
        }

        return !empty($values);
    }

    /**
     * @return string[]
     */
    public function getValues(string $name): array
    {
        return $this->getValuesByName($name) ?? [];
    }
}
