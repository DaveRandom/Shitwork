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
    private $formattedValues = [];

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @throws InvalidFormatException
     */
    private function throwInvalidType(string $name, string $expected)
    {
        $actual = \is_object($this->formattedValues[$name])
            ? \get_class($this->formattedValues[$name])
            : \gettype($this->formattedValues[$name]);

        throw new InvalidFormatException("Data for key '{$name}' is of type {$actual}, expecting {$expected}");
    }

    /** @throws InvalidFormatException */
    private static function parseBool(string $value): bool
    {
        static $valueMap = [
            'true' => true, 'on' => true, 'yes' => true, '1' => true,
            'false' => false, 'off' => false, 'no' => false, '0' => false,
        ];

        $valueLower = \strtolower($value);

        if (!isset($valueMap[$valueLower])) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a boolean");
        }

        return $valueMap[$valueLower];
    }

    /** @throws InvalidFormatException */
    private static function parseInt(string $value): int
    {
        if (!\ctype_digit($value)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as an integer");
        }

        return (int)$value;
    }

    /** @throws InvalidFormatException */
    private static function parseFloat(string $value): float
    {
        if (!\is_numeric($value)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as an float");
        }

        return (float)$value;
    }

    /** @throws InvalidFormatException */
    private static function parseDateTime(string $value, string $format): \DateTimeImmutable
    {
        if (!$result = \DateTimeImmutable::createFromFormat($format, $value)) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a date/time using the format '{$format}'");
        }

        return $result;
    }

    /** @throws InvalidFormatException */
    private static function parseUuid(string $value): UuidInterface
    {
        try {
            return Uuid::fromString($value);
        } catch (\Throwable $e) {
            throw new InvalidFormatException("Cannot parse '{$value}' as a UUID: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * @throws UndefinedValueException
     */
    public function getRawValue(string $name): ?string
    {
        if (!\array_key_exists($name, $this->values)) {
            throw new UndefinedValueException("Key '{$name}' does not exist in the collection");
        }

        return (string)$this->values[$name];
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
        if (!\array_key_exists($name, $this->formattedValues)) {
            $this->formattedValues[$name] = self::parseBool($this->getRawValue($name));
        }

        if (!\is_bool($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'boolean');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getInt(string $name): int
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $this->formattedValues[$name] = self::parseInt($this->getRawValue($name));
        }

        if (!\is_int($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'integer');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getFloat(string $name): float
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $this->formattedValues[$name] = self::parseFloat($this->getRawValue($name));
        }

        if (!\is_float($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'float');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getString(string $name): string
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $this->formattedValues[$name] = $this->getRawValue($name);
        }

        if (!\is_string($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'string');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getDateTime(string $name, string $format = self::DEFAULT_DATETIME_FORMAT): \DateTimeImmutable
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $this->formattedValues[$name] = self::parseDateTime($this->getRawValue($name), $format);
        }

        if (!($this->formattedValues[$name] instanceof \DateTimeImmutable)) {
            $this->throwInvalidType($name, 'date/time');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getUuid(string $name): UuidInterface
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $this->formattedValues[$name] = self::parseUuid($this->getRawValue($name));
        }

        if (!($this->formattedValues[$name] instanceof UuidInterface)) {
            $this->throwInvalidType($name, 'UUID');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getNullableBool(string $name): ?bool
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $raw = $this->getRawValue($name);

            $this->formattedValues[$name] = $raw !== ''
                ? self::parseBool($raw)
                : null;
        }

        if ($this->formattedValues[$name] !== null && !\is_bool($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'nullable boolean');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getNullableInt(string $name): ?int
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $raw = $this->getRawValue($name);

            $this->formattedValues[$name] = $raw !== ''
                ? self::parseInt($raw)
                : null;
        }

        if ($this->formattedValues[$name] !== null && !\is_int($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'nullable integer');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getNullableFloat(string $name): ?float
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $raw = $this->getRawValue($name);

            $this->formattedValues[$name] = $raw !== ''
                ? self::parseFloat($raw)
                : null;
        }

        if ($this->formattedValues[$name] !== null && !\is_float($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'nullable float');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getNullableString(string $name): ?string
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $raw = $this->getRawValue($name);

            $this->formattedValues[$name] = $raw !== ''
                ? $raw
                : null;
        }

        if ($this->formattedValues[$name] !== null && !\is_string($this->formattedValues[$name])) {
            $this->throwInvalidType($name, 'nullable string');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getNullableDateTime(string $name, string $format = self::DEFAULT_DATETIME_FORMAT): ?\DateTimeImmutable
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $raw = $this->getRawValue($name);

            $this->formattedValues[$name] = $raw !== ''
                ? self::parseDateTime($raw, $format)
                : null;
        }

        if ($this->formattedValues[$name] !== null && !($this->formattedValues[$name] instanceof \DateTimeImmutable)) {
            $this->throwInvalidType($name, 'nullable date/time');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function getNullableUuid(string $name): ?UuidInterface
    {
        if (!\array_key_exists($name, $this->formattedValues)) {
            $raw = $this->getRawValue($name);

            $this->formattedValues[$name] = $raw !== ''
                ? self::parseUuid($raw)
                : null;
        }

        if ($this->formattedValues[$name] !== null && !($this->formattedValues[$name] instanceof UuidInterface)) {
            $this->throwInvalidType($name, 'nullable UUID');
        }

        return $this->formattedValues[$name];
    }

    /** @inheritdoc */
    public function toArray(): array
    {
        return $this->values;
    }
}
