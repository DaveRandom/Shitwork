<?php declare(strict_types=1);

namespace Shitwork;

use Shitwork\Exceptions\UndefinedValueException;

final class HeaderCollection
{
    private $headers = [];

    public static function createFromSuperglobals(): self
    {
        $headers = [];

        foreach ((array)$_SERVER as $key => $value) {
            if (\strtoupper(\substr($key, 0, 5)) === 'HTTP_') {
                $headers[\preg_replace('#_+#', '-', \substr($key, 5))] = $value;
            }
        }

        return new self($headers);
    }

    public function __construct(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->headers[$this->normalizeName($name)] = $value;
        }
    }

    private function normalizeName(string $name): string
    {
        return \strtolower($name);
    }

    public function contains(string $name): bool
    {
        return \array_key_exists($this->normalizeName($name), $this->headers);
    }

    /**
     * @throws UndefinedValueException
     */
    public function get(string $name): string
    {
        $normalName = $this->normalizeName($name);

        if (!\array_key_exists($normalName, $this->headers)) {
            throw new UndefinedValueException("Header '{$name}' not defined in the collection");
        }

        return $this->headers[$normalName];
    }

    public function toArray(): array
    {
        return $this->headers;
    }
}
