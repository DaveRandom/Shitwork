<?php declare(strict_types=1);

namespace Shitwork;

use DaveRandom\Enum\Enum;
use Shitwork\Exceptions\LogicError;

final class HttpStatus extends Enum
{
    private const OVERRIDE_MESSAGES = [
        self::OK => 'OK',
    ];

    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const NO_CONTENT = 204;
    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const SEE_OTHER = 303;
    public const TEMPORARY_REDIRECT = 307;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    public const INTERNAL_SERVER_ERROR = 500;

    /**
     * @throws LogicError
     */
    public static function getMessage(int $status): string
    {
        if (!self::valueExists($status)) {
            throw new LogicError("Invalid or unknown HTTP status: {$status}");
        }

        return self::OVERRIDE_MESSAGES[$status]
            ?? \ucwords(\strtolower(\strtr(self::parseValue($status), '_', ' ')));
    }

    /**
     * @throws LogicError
     */
    public static function setHeader(int $status): void
    {
        \header("HTTP/1.1 {$status} " . self::getMessage($status));
    }
}
