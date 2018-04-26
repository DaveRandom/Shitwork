<?php declare(strict_types=1);

namespace Shitwork;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shitwork\Exceptions\InvalidFormatException;
use Shitwork\Exceptions\UndefinedValueException;

final class ValueMap implements DataRecord
{
    private const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    private function describeType($value)
    {
        return \is_object($value)
            ? \get_class($value)
            : \gettype($value);
    }

    /** @throws InvalidFormatException */
    private function parseBool($value): bool
    {
        static $valueMap = [
            'true' => true, 'on' => true, 'yes' => true, '1' => true,
            'false' => false, 'off' => false, 'no' => false, '0' => false,
        ];

        if (\is_bool($value) || $value === 0 || $value === 1) {
            return (bool)$value;
        }

        if (!\is_string($value)) {
            throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to boolean");
        }

        $result = $valueMap[\strtolower($value)] ?? null;

        if (!\is_bool($result)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a boolean");
        }

        return $result;
    }

    /** @throws InvalidFormatException */
    private function parseInt($value): int
    {
        if (\is_int($value) || \is_float($value)) {
            return (int)$value;
        }

        if (!\is_string($value)) {
            throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to float");
        }

        if (!\ctype_digit($value)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as an integer");
        }

        return (int)$value;
    }

    /** @throws InvalidFormatException */
    private function parseFloat($value): float
    {
        if (\is_int($value) || \is_float($value)) {
            return (float)$value;
        }

        if (!\is_string($value)) {
            throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to float");
        }

        if (!\is_numeric($value)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a float");
        }

        return (float)$value;
    }

    /** @throws InvalidFormatException */
    private function parseString($value): string
    {
        if (\is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (\is_scalar($value) || (\is_object($value) && !\method_exists($value, '__toString'))) {
            return (string)$value;
        }

        throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to string");
    }

    /** @throws InvalidFormatException */
    private function parseDateTime($value, string $format): \DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($value);
        }

        // int, float and numeric string are treated as timestamps
        if (\is_numeric($value)) {
            return \DateTimeImmutable::createFromFormat('U.u', \sprintf('%.6F', $value));
        }

        if (!\is_scalar($value)) {
            throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to date/time");
        }

        if (!$result = \DateTimeImmutable::createFromFormat($format, $value)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a date/time using the format '{$format}'");
        }

        return $result;
    }

    /** @throws InvalidFormatException */
    private function parseUuid($value): UuidInterface
    {
        if ($value instanceof UuidInterface) {
            return $value;
        }

        if (\is_int($value)) {
            return Uuid::fromInteger($value);
        }

        if (!\is_string($value)) {
            throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to UUID");
        }

        try {
            return \strlen($value) === 16
                ? Uuid::fromBytes($value)
                : Uuid::fromString($value);
        } catch (\Throwable $e) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a UUID: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /** @throws InvalidFormatException */
    private function parseArray($value): array
    {
        if (\is_array($value)) {
            return $value;
        }

        throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to array");
    }

    /** @throws InvalidFormatException */
    private function parseObject($value, ?string $className): object
    {
        $description = 'object' . ($className !== null ? ' of class ' . $className : '');

        if (!\is_object($value)) {
            throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to {$description}");
        }

        if ($className !== null && !($value instanceof $className)) {
            throw new InvalidFormatException("Cannot convert object of class " . \get_class($value) . " to {$description}");
        }

        return $value;
    }

    /**
     * @throws UndefinedValueException
     * @return mixed
     */
    public function getRawValue(string $name)
    {
        if (!\array_key_exists($name, $this->values)) {
            throw new UndefinedValueException("Key '{$name}' does not exist in the collection");
        }

        return $this->values[$name];
    }

    /** @inheritdoc */
    public function contains(string ...$names): bool
    {
        foreach ($names as $name) {
            if (!\array_key_exists($name, $this->values)) {
                return false;
            }
        }

        return true;
    }

    /** @inheritdoc */
    public function getBool(string $name): bool
    {
        return $this->parseBool($this->getRawValue($name));
    }

    /** @inheritdoc */
    public function getInt(string $name): int
    {
        return $this->parseInt($this->getRawValue($name));
    }

    /** @inheritdoc */
    public function getFloat(string $name): float
    {
        return $this->parseFloat($this->getRawValue($name));
    }

    /** @inheritdoc */
    public function getString(string $name): string
    {
        return $this->parseString($this->getRawValue($name));
    }

    /** @inheritdoc */
    public function getDateTime(string $name, string $format = null): \DateTimeImmutable
    {
        return $this->parseDateTime($this->getRawValue($name), $format ?? self::DEFAULT_DATETIME_FORMAT);
    }

    /** @inheritdoc */
    public function getUuid(string $name): UuidInterface
    {
        return $this->parseUuid($this->getRawValue($name));
    }

    /** @inheritdoc */
    public function getArray(string $name): array
    {
        return $this->parseArray($this->getRawValue($name));
    }

    /** @inheritdoc */
    public function getObject(string $name, string $className = null): object
    {
        return $this->parseObject($this->getRawValue($name), $className);
    }

    /** @inheritdoc */
    public function getNullableBool(string $name): ?bool
    {
        $raw = $this->getRawValue($name);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseBool($raw);
    }

    /** @inheritdoc */
    public function getNullableInt(string $name): ?int
    {
        $raw = $this->getRawValue($name);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseInt($raw);
    }

    /** @inheritdoc */
    public function getNullableFloat(string $name): ?float
    {
        $raw = $this->getRawValue($name);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseFloat($raw);
    }

    /** @inheritdoc */
    public function getNullableString(string $name): ?string
    {
        $raw = $this->getRawValue($name);

        if ($raw === null) {
            return null;
        }

        return $this->parseString($raw);
    }

    /** @inheritdoc */
    public function getNullableDateTime(string $name, string $format = null): ?\DateTimeImmutable
    {
        $raw = $this->getRawValue($name);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseDateTime($raw, $format ?? self::DEFAULT_DATETIME_FORMAT);
    }

    /** @inheritdoc */
    public function getNullableUuid(string $name): ?UuidInterface
    {
        $raw = $this->getRawValue($name);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseUuid($raw);
    }

    /** @inheritdoc */
    public function getNullableArray(string $name): array
    {
        $raw = $this->getRawValue($name);

        if ($raw === null) {
            return null;
        }

        return $this->parseArray($raw);
    }

    /** @inheritdoc */
    public function getNullableObject(string $name, string $className = null): ?object
    {
        $raw = $this->getRawValue($name);

        if ($raw === null) {
            return null;
        }

        return $this->parseObject($raw, $className);
    }

    /** @inheritdoc */
    public function toArray(): array
    {
        return $this->values;
    }
}
