<?php declare(strict_types=1);

namespace Shitwork;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Shitwork\Exceptions\InvalidFormatException;
use Shitwork\Exceptions\LogicError;
use Shitwork\Exceptions\OutOfRangeException;
use Shitwork\Exceptions\InvalidKeyException;

class ValueMap implements DataRecord
{
    private const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $values = [];
    private $names = [];
    private $keysByName = [];

    private function initFromAssociativeArray(array $data)
    {
        $key = 0;

        foreach ($data as $name => $value) {
            $this->values[$key] = $value;
            $this->names[$key] = $name;
            $this->keysByName[$name] = $this->keysByName[$name] ?? $key;
            $key++;
        }

        return $this;
    }

    public static function fromAssociativeArray(array $data)
    {
        return (new static([]))->initFromAssociativeArray($data);
    }

    private function processCtorArgs(array $values, array $names)
    {
        if (\count($values) !== \count($names)) {
            throw new LogicError("Number of names must match number of values");
        }

        $this->values = \array_values($values);
        $key = 0;

        foreach ($names as $name) {
            $this->names[$key] = $name;
            $this->keysByName[$name] = $this->keysByName[$name] ?? $key;

            $key++;
        }
    }

    public function __construct(array $values, array $names = null)
    {
        if ($values) {
            $this->processCtorArgs($values, $names ?? \array_keys($values));
        }
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

        if (!\is_string($value)) {
            throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to date/time");
        }

        if (!$result = \DateTimeImmutable::createFromFormat($format, $value)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a date/time using the format '{$format}'");
        }

        return $result;
    }

    /** @throws InvalidFormatException */
    private function parseTime($value): TimeSpan
    {
        if ($value instanceof TimeSpan) {
            return $value;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return TimeSpan::createFromDateTime($value);
            }

            // int, float and numeric string are treated as number of seconds
            if (\is_numeric($value)) {
                return TimeSpan::createFromSeconds($value);
            }

            if (!\is_string($value)) {
                throw new InvalidFormatException("Cannot convert value of type {$this->describeType($value)} to time");
            }

            return TimeSpan::createFromString($value);
        } catch (OutOfRangeException $e) {
            throw new InvalidFormatException("Failed to convert '{$value}' to time: {$e->getMessage()}");
        }
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
     * @throws InvalidKeyException
     * @return mixed
     */
    public function getRawValue($key)
    {
        if (!\array_key_exists($key, $this->values)) {
            if (!isset($this->keysByName[$key])) {
                throw new InvalidKeyException("Key '{$key}' does not exist in the collection");
            }

            $key = $this->keysByName[$key];
        }

        return $this->values[$key];
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
    public function getBool($key): bool
    {
        return $this->parseBool($this->getRawValue($key));
    }

    /** @inheritdoc */
    public function getInt($key): int
    {
        return $this->parseInt($this->getRawValue($key));
    }

    /** @inheritdoc */
    public function getFloat($key): float
    {
        return $this->parseFloat($this->getRawValue($key));
    }

    /** @inheritdoc */
    public function getString($key): string
    {
        return $this->parseString($this->getRawValue($key));
    }

    /** @inheritdoc */
    public function getDateTime($key, string $format = null): \DateTimeImmutable
    {
        return $this->parseDateTime($this->getRawValue($key), $format ?? self::DEFAULT_DATETIME_FORMAT);
    }

    /** @inheritdoc */
    public function getTimeSpan($key): TimeSpan
    {
        return $this->parseTime($this->getRawValue($key));
    }

    /** @inheritdoc */
    public function getUuid($key): UuidInterface
    {
        return $this->parseUuid($this->getRawValue($key));
    }

    /** @inheritdoc */
    public function getArray($key): array
    {
        return $this->parseArray($this->getRawValue($key));
    }

    /** @inheritdoc */
    public function getObject($key, string $className = null): object
    {
        return $this->parseObject($this->getRawValue($key), $className);
    }

    /** @inheritdoc */
    public function getNullableBool($key): ?bool
    {
        $raw = $this->getRawValue($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseBool($raw);
    }

    /** @inheritdoc */
    public function getNullableInt($key): ?int
    {
        $raw = $this->getRawValue($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseInt($raw);
    }

    /** @inheritdoc */
    public function getNullableFloat($key): ?float
    {
        $raw = $this->getRawValue($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseFloat($raw);
    }

    /** @inheritdoc */
    public function getNullableString($key): ?string
    {
        $raw = $this->getRawValue($key);

        if ($raw === null) {
            return null;
        }

        return $this->parseString($raw);
    }

    /** @inheritdoc */
    public function getNullableDateTime($key, string $format = null): ?\DateTimeImmutable
    {
        $raw = $this->getRawValue($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseDateTime($raw, $format ?? self::DEFAULT_DATETIME_FORMAT);
    }

    /** @inheritdoc */
    public function getNullableTimeSpan($key): ?TimeSpan
    {
        $raw = $this->getRawValue($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseTime($raw);
    }

    /** @inheritdoc */
    public function getNullableUuid($key): ?UuidInterface
    {
        $raw = $this->getRawValue($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        return $this->parseUuid($raw);
    }

    /** @inheritdoc */
    public function getNullableArray($key): array
    {
        $raw = $this->getRawValue($key);

        if ($raw === null) {
            return null;
        }

        return $this->parseArray($raw);
    }

    /** @inheritdoc */
    public function getNullableObject($key, string $className = null): ?object
    {
        $raw = $this->getRawValue($key);

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

    public function count(): int
    {
        return \count($this->values);
    }

    /** @inheritdoc */
    public function getName(int $key): string
    {
        if (!isset($this->names[$key])) {
            throw new InvalidKeyException("Key '{$key}' does not exist in the collection");
        }

        return $this->names[$key];
    }

    /** @inheritdoc */
    public function getOrdinal(string $name): int
    {
        if (!isset($this->keysByName[$name])) {
            throw new InvalidKeyException("Name '{$name}' does not exist in the collection");
        }

        return $this->keysByName[$name];
    }
}
