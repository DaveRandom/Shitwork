<?php declare(strict_types=1);

namespace Shitwork;

use Shitwork\Collections\ImmutableArrayAccess;
use Shitwork\Exceptions\InvalidKeyException;

final class HeaderCollection implements \ArrayAccess, \IteratorAggregate
{
    use ImmutableArrayAccess;

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
     * @throws InvalidKeyException
     */
    public function get(string $name, string $default = null, bool &$exists = null): ?string
    {
        $normalName = $this->normalizeName($name);

        if ($exists = \array_key_exists($normalName, $this->headers)) {
            return $this->headers[$normalName];
        }

        if (\func_num_args() > 1) {
            return $default;
        }

        throw new InvalidKeyException("Header '{$name}' does not exist in the collection and no default value supplied");
    }

    public function toArray(): array
    {
        return $this->headers;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->headers);
    }

    public function offsetExists($name): bool
    {
        return $this->contains($name);
    }

    /**
     * @param string $name
     * @throws InvalidKeyException
     */
    public function offsetGet($name): string
    {
        return $this->get($name);
    }
}
