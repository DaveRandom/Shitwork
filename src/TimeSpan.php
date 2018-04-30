<?php declare(strict_types=1);

namespace Shitwork;

use Shitwork\Exceptions\InvalidFormatException;
use Shitwork\Exceptions\OutOfRangeException;

final class TimeSpan
{
    private $hours;
    private $minutes;
    private $seconds;
    private $microseconds;

    /**
     * @throws InvalidFormatException
     * @throws OutOfRangeException
     */
    public static function createFromString(string $time): self
    {
        if (!\preg_match('/^([0-9]+)(?::([0-9]+))?(?::([0-9]+))?(?:\.([0-9]+))?$/', $time, $matches)) {
            throw new InvalidFormatException("Failed to parse '{$time}' as a valid time span");
        }

        $hours = (int)$matches[1];
        $minutes = (int)($matches[2] ?? 0);
        $seconds = (int)($matches[3] ?? 0);

        if (isset($matches[4])) {
            $microseconds = (int)(+"0.{$matches[4]}" * 1000000);
        }

        return new self($hours, $minutes, $seconds, $microseconds ?? 0);
    }

    /**
     * @param int|float|string $seconds
     * @throws InvalidFormatException
     * @throws OutOfRangeException
     */
    public static function createFromSeconds($seconds): self
    {
        if (!\is_numeric($seconds)) {
            throw new InvalidFormatException('Seconds must be an integer, float or numeric string');
        }

        $totalSeconds = +$seconds;

        if ($totalSeconds < 0) {
            throw new OutOfRangeException("Seconds must be greater or equal to than zero");
        }

        if (\is_float($totalSeconds)) {
            $secsInt = (int)$totalSeconds;
            $microseconds = (int)(($totalSeconds - $secsInt) * 1000000);
            $totalSeconds = $secsInt;
        }

        $hours = \intdiv($totalSeconds, 3600);
        $totalSeconds %= 3600;
        $minutes = \intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return new self($hours, $minutes, $seconds, $microseconds ?? 0);
    }

    /**
     * @throws OutOfRangeException
     */
    public static function createFromMicroseconds(int $microseconds): self
    {
        if ($microseconds < 0) {
            throw new OutOfRangeException("Microseconds must be greater or equal to than zero");
        }

        $totalSeconds = \intdiv($microseconds, 1000000);
        $microseconds %= 1000000;

        $hours = \intdiv($totalSeconds, 3600);
        $totalSeconds %= 3600;
        $minutes = \intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return new self($hours, $minutes, $seconds, $microseconds);
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
    public function __construct(int $hours = 0, int $minutes = 0, int $seconds = 0, int $microseconds = 0)
    {
        if ($hours < 0) {
            throw new OutOfRangeException("Hours must be greater than or equal to zero");
        }

        if ($minutes < 0) {
            throw new OutOfRangeException("Minutes must be greater than or equal to zero");
        }

        if ($seconds < 0) {
            throw new OutOfRangeException("Seconds must be greater than or equal to zero");
        }

        if ($microseconds < 0) {
            throw new OutOfRangeException("Microseconds must be greater than or equal to zero");
        }

        if ($microseconds > 999999) {
            $seconds += \intdiv($microseconds, 1000000);
            $microseconds %= 1000000;
        }

        if ($seconds > 59) {
            $minutes += \intdiv($seconds, 60);
            $seconds %= 60;
        }

        if ($minutes > 59) {
            $hours += \intdiv($minutes, 60);
            $minutes %= 0;
        }

        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
        $this->microseconds = $microseconds;
    }

    public function __toString()
    {
        return \sprintf(
            '%02d:%02d:%02d.%06d',
            $this->hours, $this->minutes, $this->seconds, $this->microseconds
        );
    }

    public function format(string $format): string
    {
        $result = '';

        for ($i = 0, $l = \strlen($format); $i < $l; $i++) {
            switch ($format[$i]) {
                case 'g': case 'G':
                    $result .= (string)$this->hours;
                    break;

                case 'h': case 'H':
                    $result .= \sprintf('%02d', $this->hours);
                    break;

                case 'i':
                    $result .= \sprintf('%02d', $this->minutes);
                    break;

                case 's':
                    $result .= \sprintf('%02d', $this->seconds);
                    break;

                case 'u':
                    $result .= \sprintf('%06d', $this->microseconds);
                    break;

                case '\\':
                    $result .= $format[++$i];
                    break;

                default:
                    $result .= $format[$i];
                    break;
            }
        }

        return $result;
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

    public function getMicroseconds(): int
    {
        return $this->microseconds;
    }

    public function getTotalMinutes(): int
    {
        return ($this->hours * 60) + $this->minutes;
    }

    public function getTotalSeconds(): int
    {
        return ($this->hours * 3600) + ($this->minutes * 60) + $this->seconds;
    }

    public function getTotalMicroseconds(): int
    {
        return ($this->getTotalSeconds() * 1000000) + $this->microseconds;
    }
}
