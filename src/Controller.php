<?php declare(strict_types = 1);

namespace Shitwork;

use Shitwork\Routing\InvalidRouteException;

abstract class Controller
{
    private const RESPONDER_NONE = 0;
    private const RESPONDER_JSON = 1;

    private $responderType = self::RESPONDER_NONE;
    private $useExtraVars = false;
    private $headers = [];
    private $responseSent = false;

    protected function sendHeaders(array $extraHeaders = []): void
    {
        foreach ($extraHeaders as $name => $value) {
            \header("{$name}: {$value}");
        }

        foreach ($this->headers as [$name, $value]) {
            \header("{$name}: {$value}");
        }
    }

    protected function sendJSONResponse(bool $success, array $data = []): void
    {
        if ($this->responseSent) {
            return;
        }

        $this->responseSent = true;
        $this->headers['content-type'] = $this->headers['content-type'] ?? ['Content-Type', 'application/json'];

        $this->sendHeaders();

        echo \json_encode(['success' => $success] + $data);
    }

    protected function executeJSONResponder(callable $callback): void
    {
        try {
            $data = (array)$callback();
            $success = true;
        } catch (\Throwable $e) {
            if (http_response_line_from_exception($e) === HttpStatus::INTERNAL_SERVER_ERROR) {
                \error_log((string)$e);
            }

            $data = ['message' => $e->getMessage()];
            $success = false;
        } finally {
            $this->sendJSONResponse($success, $data);
        }
    }

    private function processDocComment(DocComment $comment): void
    {
        $this->responderType = $comment->hasFlag('jsonResponder')
            ? self::RESPONDER_JSON
            : self::RESPONDER_NONE;

        $this->useExtraVars = $comment->hasFlag('extraVars');

        foreach ($comment->getValues('header') as $header) {
            if (!\preg_match('/^(\S+)\s+(\S.*)$/', $header, $parts)) {
                continue;
            }

            $this->headers[\strtolower($parts[1])] = [$parts[1], $parts[2]];
        }
    }

    /**
     * @throws InvalidRouteException
     */
    public function __call(string $name, array $arguments)
    {
        try {
            $object = new \ReflectionClass($this);

            if (false !== $comment = $object->getDocComment()) {
                $this->processDocComment(DocComment::parse($comment));
            }

            $method = $object->getMethod($name);

            if (false !== $comment = $method->getDocComment()) {
                $this->processDocComment(DocComment::parse($comment));
            }

            if ($this->responderType === self::RESPONDER_NONE) {
                throw new InvalidRouteException($this, $name, 'No responder set');
            }

            if (!$this->useExtraVars) {
                $arguments = [\array_pop($arguments)];
            }

            $this->executeJSONResponder(function() use($method, $arguments) {
                return $method->getClosure($this)(...$arguments);
            });
        } catch (\ReflectionException $e) {
            throw new InvalidRouteException($this, $name, $e->getMessage(), $e);
        }
    }
}
