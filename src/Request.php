<?php declare(strict_types = 1);

namespace Shitwork;

use Shitwork\Exceptions\LogicError;

final class Request
{
    /**
     * @var string
     */
    private $remoteAddr;

    /**
     * @var int
     */
    private $remotePort;

    /**
     * @var string
     */
    private $authUser;

    /**
     * @var string
     */
    private $authPass;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $protocolName;

    /**
     * @var string
     */
    private $protocolVersion;

    /**
     * @var bool
     */
    private $secure;

    /**
     * @var string
     */
    private $absoluteURI;

    /**
     * @var string
     */
    private $rawURI;

    /**
     * @var string
     */
    private $uriPath;

    /**
     * @var string
     */
    private $baseURI;

    /**
     * @var string
     */
    private $controllerName;

    /**
     * @var string
     */
    private $actionName;

    /**
     * @var string[]
     */
    private $pathParams;

    /**
     * @var string[]
     */
    private $urlParams;

    /**
     * @var string[]
     */
    private $formParams;

    /**
     * @var string[]
     */
    private $cookies;

    /**
     * @var string[]
     */
    private $headers;

    /**
     * @var RequestBody
     */
    private $body;

    /**
     * @var array
     */
    private $files;

    /**
     * @throws \Error
     */
    public function __construct()
    {
        $this->urlParams = (array)$_GET;
        $this->formParams = (array)$_POST;
        $this->cookies = (array)$_COOKIE;
        $this->files = (array)$_FILES;

        $this->remoteAddr = $_SERVER['REMOTE_ADDR'];
        $this->remotePort = (int)$_SERVER['REMOTE_PORT'];
        $this->authUser = $_SERVER['PHP_AUTH_USER'] ?? null;
        $this->authPass = $_SERVER['PHP_AUTH_PW'] ?? null;
        $this->method = $_SERVER['REQUEST_METHOD'];
        if (\preg_match('#^([^/]+)/((?:\d|\.)+)$#', $_SERVER['SERVER_PROTOCOL'], $matches)) {
            $this->protocolName = $matches[1];
            $this->protocolVersion = $matches[2];
        }

        $this->storeURI($_SERVER['REQUEST_URI']);
        $this->storeHeaders($_SERVER);

        $this->body = new RequestBody((string)\file_get_contents('php://input'), $this->getHeader('Content-Type'));

        $this->secure = !empty($_SERVER['HTTPS']);
        $this->baseURI = ($this->secure ? 'https' : 'http') . '://' . $this->getHeader('Host');

        $this->absoluteURI = $this->baseURI . $this->rawURI;
    }

    /**
     * @throws \Error
     */
    private function storeURI(string $uri): void
    {
        static $splitExpr = /** @lang text */
        "#
            ^
            (?<path>
                /
                (?<controller>[^/?]+)?
                /?
                (?<action>[^/?]+)?
                (?:
                    /
                    (?<params>[^?]+)
                )?
            )
            (?=\\?|$)
        #x";

        if (!\preg_match($splitExpr, $uri, $matches)) {
            throw new \Error('Malformed URI');
        }

        $ucWords = function($match) {
            return \strtoupper($match[1]) . \strtolower($match[2]);
        };

        $this->rawURI = $uri;
        $this->uriPath = $matches['path'];
        $this->controllerName = !empty($matches['controller'])
            ? \preg_replace_callback('#(?:^|-)([a-z])([^-]*)#', $ucWords, $matches['controller'])
            : 'Index';
        $this->actionName = !empty($matches['action'])
            ? \preg_replace_callback('#-([a-z])([^-]*)#', $ucWords, $matches['action'])
            : 'default';
        $this->pathParams = isset($matches['params']) && $matches['params'] !== ''
            ? \preg_split('#/+#', $matches['params'], -1, PREG_SPLIT_NO_EMPTY)
            : [];
    }

    private function storeHeaders(array $server)
    {
        foreach ($server as $key => $value) {
            if (\strtoupper(\substr($key, 0, 5)) === 'HTTP_') {
                $this->headers[\strtolower(\preg_replace('#_+#', '-', \substr($key, 5)))] = $value;
            }
        }
    }

    public function getProtocolName(): string
    {
        return $this->protocolName;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function getRemoteAddr(): string
    {
        return $this->remoteAddr;
    }

    public function getRemotePort(): int
    {
        return $this->remotePort;
    }

    /**
     * @return string|null
     */
    public function getAuthUser()
    {
        return $this->authUser;
    }

    /**
     * @return string|null
     */
    public function getAuthPass()
    {
        return $this->authPass;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function getAbsoluteURI(): string
    {
        return $this->absoluteURI;
    }

    public function getRawURI(): string
    {
        return $this->rawURI;
    }

    public function getBaseURI(): string
    {
        return $this->baseURI;
    }

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function getURIPath(): string
    {
        return $this->uriPath;
    }

    public function hasPathParam(int $index): bool
    {
        return isset($this->pathParams[$index]);
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function getPathParam(int $index)
    {
        return $this->pathParams[$index] ?? null;
    }

    /**
     * @return string[]
     */
    public function getAllPathParams(): array
    {
        return $this->pathParams;
    }

    public function hasURLParam(string $key): bool
    {
        return isset($this->urlParams[$key]);
    }

    public function hasURLParams(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($this->urlParams[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getURLParam(string $key)
    {
        return $this->urlParams[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getAllURLParams(): array
    {
        return $this->urlParams;
    }

    public function hasFormParam(string $key): bool
    {
        return isset($this->formParams[$key]);
    }

    public function hasFormParams(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($this->formParams[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getFormParam(string $key)
    {
        return $this->formParams[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getAllFormParams(): array
    {
        return $this->formParams;
    }

    public function hasCookie(string $key): bool
    {
        return isset($this->cookies[$key]);
    }

    public function hasCookies(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($this->cookies[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getCookie(string $key)
    {
        return $this->cookies[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getAllCookies(): array
    {
        return $this->cookies;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[\strtolower($name)]);
    }

    public function hasHeaders(string ...$names): bool
    {
        foreach ($names as $name) {
            if (!isset($this->headers[\strtolower($name)])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader($name)
    {
        return $this->headers[\strtolower($name)] ?? null;
    }

    public function getAllHeaders(): array
    {
        return $this->headers;
    }

    public function getAllFileControls(): array
    {
        //todo
        return $this->files;
    }

    public function hasBody(): bool
    {
        return $this->body->getLength() > 0;
    }

    public function getBody(): RequestBody
    {
        return $this->body;
    }

    /**
     * @throws LogicError
     */
    private function resolvePartialRedirectUri(array $parts, string $uri): string
    {
        if (!empty($parts['host'])) {
            return ($this->secure ? 'https:' : 'http:') . $uri;
        }

        if (!empty($parts['path'])) {
            if ($parts['path'][0] !== '/') {
                throw new LogicError('Path-only redirect URIs must be absolute: ' . $uri);
            }

            return $this->baseURI . $uri;
        }

        if (!empty($parts['query'])) {
            return $this->baseURI . $this->uriPath . $uri;
        }

        if (!empty($parts['fragment'])) {
            return $this->baseURI . $this->rawURI . $uri;
        }

        throw new LogicError('Invalid redirect target URI: ' . $uri);
    }

    /**
     * @todo This probably doesn't belong here
     * @param string $uri
     * @param int $code
     * @throws LogicError
     */
    public function redirect(string $uri, int $code = HttpStatus::SEE_OTHER)
    {
        if (!\in_array($code, [HttpStatus::MOVED_PERMANENTLY, HttpStatus::FOUND, HttpStatus::SEE_OTHER, HttpStatus::TEMPORARY_REDIRECT])) {
            throw new LogicError('Unknown redirect response code: ' . $code);
        }

        if (!$parts = \parse_url($uri)) {
            throw new LogicError('Invalid redirect target URI: ' . $uri);
        }

        if (empty($parts['scheme'])) {
            $uri = $this->resolvePartialRedirectUri($parts, $uri);
        }

        HttpStatus::setHeader($code);
        \header(\sprintf('Location: %s', $uri));
    }
}
