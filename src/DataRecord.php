<?php declare(strict_types=1);

namespace Shitwork;

use Ramsey\Uuid\UuidInterface;
use Shitwork\Exceptions\InvalidFormatException;
use Shitwork\Exceptions\UndefinedValueException;

interface DataRecord
{
    public function contains(string ...$names): bool;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getBool(string $name): bool;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getInt(string $name): int;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getFloat(string $name): float;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getString(string $name): string;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getDateTime(string $name, string $format): \DateTimeImmutable;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getUuid(string $name): UuidInterface;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getNullableBool(string $name): ?bool;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getNullableInt(string $name): ?int;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getNullableFloat(string $name): ?float;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getNullableString(string $name): ?string;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getNullableDateTime(string $name, string $format): ?\DateTimeImmutable;

    /**
     * @throws UndefinedValueException
     * @throws InvalidFormatException
     */
    public function getNullableUuid(string $name): ?UuidInterface;

    public function toArray(): array;
}
