<?php declare(strict_types = 1);

namespace Shitwork;

use Shitwork\Exceptions\LogicError;

final class Session implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array
     */
    private $data;

    public function __construct()
    {
        \session_start();
        $this->data = $_SESSION;
    }

    public function __destruct()
    {
        if ($this->isOpen()) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->close();
        }
    }

    public function isOpen()
    {
        return isset($this->data);
    }

    /**
     * @throws LogicError
     */
    public function close()
    {
        if (!$this->isOpen()) {
            throw new LogicError('Cannot close session: not open');
        }

        $_SESSION = $this->data;
        \session_write_close();
        $this->data = null;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function count()
    {
        return \count($this->data);
    }

    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function offsetGet($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function &getAll()
    {
        return $this->data;
    }

    public function set($key, $value)
    {
        return $this->data[(string)$key] = $value;
    }

    public function __set($key, $value)
    {
        $this->data[(string)$key] = $value;
    }

    public function offsetSet($key, $value)
    {
        $this->data[(string)$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
