<?php declare(strict_types=1);

namespace Shitwork;

final class RequestBody
{
    private $data;
    private $type;
    private $length;

    public function __construct(string $data, ?string $type)
    {
        $this->data = $data !== '' ? $data : null;
        $this->type = $type;
        $this->length = \strlen($data);
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
