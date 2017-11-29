<?php declare(strict_types=1);

namespace Shitwork;

final class HttpStatus extends Enum
{
    private const MESSAGES = [
        self::OK => 'OK',
        self::CREATED => 'Created',
        self::ACCEPTED => 'Accepted',
        self::NO_CONTENT => 'No Content',
        self::BAD_REQUEST => 'Bad Request',
        self::UNAUTHORIZED => 'Unauthorized',
        self::FORBIDDEN => 'Forbidden',
        self::NOT_FOUND => 'Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
    ];

    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const NO_CONTENT = 204;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    public const INTERNAL_SERVER_ERROR = 500;

    public static function getMessage(int $status): string
    {
        if (!self::isValid($status)) {
            throw new \LogicException("Invalid or unknown HTTP status: {$status}");
        }

        return self::MESSAGES[$status];
    }

    public static function setHeader(int $status): void
    {
        \header("HTTP/1.1 {$status} " . self::getMessage($status));
    }

    public static function isValid(int $status): bool
    {
        return \array_key_exists($status, self::MESSAGES);
    }
}
