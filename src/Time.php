<?php declare(strict_types=1);

namespace Shitwork;

use Shitwork\Exceptions\InvalidFormatException;
use Shitwork\Exceptions\OutOfRangeException;

final class Time
{
    private $hours;
    private $minutes;
    private $seconds;

    private $dateTime;

    /**
     * @throws InvalidFormatException
     * @throws OutOfRangeException
     */
    public static function createFromString(string $time): self
    {
        if (!\preg_match('/^([0-9]{1,2})(?::([0-9]{1,2}))?(?::([0-9]{1,2}))?$/', $time, $matches)) {
            throw new InvalidFormatException("Failed to parse '{$time}' as a valid time");
        }

        return new self((int)$matches[1], (int)($matches[2] ?? 0), (int)($matches[3] ?? 0));
    }

    /**
     * @throws InvalidFormatException
     * @throws OutOfRangeException
     */
    public static function createFromSeconds($time): self
    {
        if (!\is_numeric($time)) {
            throw new InvalidFormatException('Seconds must be an integer, float or numeric string');
        }

        $totalSeconds = (int)$time;

        if ($totalSeconds < 0 || $totalSeconds > 86399) {
            throw new OutOfRangeException("Seconds must be in the range 0 - 86399");
        }

        $hours = (int)($totalSeconds / 3600);
        $totalSeconds %= 3600;
        $minutes = (int)($totalSeconds / 60);
        $seconds = (int)($totalSeconds % 60);

        return new self($hours, $minutes, $seconds);
    }

    /**
     * @throws OutOfRangeException
     */
    public static function createFromDateTime(\DateTimeInterface $time): self
    {
        return new self((int)$time->format('H'), (int)$time->format('i'), (int)$time->format('s'));
    }

    /**
     * @throws OutOfRangeException
     */
    public function __construct(int $hours = 0, int $minutes = 0, int $seconds = 0)
    {
        if ($hours < 0 || $hours > 23) {
            throw new OutOfRangeException("Hours must be in the range 0 - 23");
        }

        if ($minutes < 0 || $minutes > 59) {
            throw new OutOfRangeException("Minutes must be in the range 0 - 59");
        }

        if ($seconds < 0 || $seconds > 59) {
            throw new OutOfRangeException("Seconds must be in the range 0 - 59");
        }

        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
    }

    public function __toString()
    {
        return $this->format('H:i:s');
    }

    public function format(string $format)
    {
        return (
            $this->dateTime ?? \DateTimeImmutable::createFromFormat('!H:i:s', \sprintf(
                '%02d:%02d:%02d', $this->hours, $this->minutes, $this->seconds
            ))
        )->format($format);
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function getTotalSeconds(): int
    {
        return ($this->hours * 3600) + ($this->minutes * 60) + $this->seconds;
    }
}
