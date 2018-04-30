<?php declare(strict_types=1);

namespace Shitwork;

use Ramsey\Uuid\UuidInterface;
use Shitwork\Exceptions\InvalidFormatException;
use Shitwork\Exceptions\InvalidKeyException;

interface DataRecord extends \Countable
{
    function contains(string ...$names): bool;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getBool($key): bool;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getInt($key): int;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getFloat($key): float;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getString($key): string;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getDateTime($key, string $format = null): \DateTimeImmutable;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getTimeSpan($key): TimeSpan;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getUuid($key): UuidInterface;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getArray($key): array;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getObject($key, string $className = null): object;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableBool($key): ?bool;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableInt($key): ?int;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableFloat($key): ?float;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableString($key): ?string;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableDateTime($key, string $format = null): ?\DateTimeImmutable;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableTimeSpan($key): ?TimeSpan;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableUuid($key): ?UuidInterface;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableArray($key): array;

    /**
     * @throws InvalidKeyException
     * @throws InvalidFormatException
     */
    function getNullableObject($key, string $className = null): ?object;

    function toArray(): array;

    /**
     * @throws InvalidKeyException
     */
    function getName(int $key): string;

    /**
     * @throws InvalidKeyException
     */
    function getOrdinal(string $name): int;
}
