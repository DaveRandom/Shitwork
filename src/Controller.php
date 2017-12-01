<?php declare(strict_types = 1);

namespace Shitwork;

use Shitwork\Routing\Exceptions\InvalidRouteException;

abstract class Controller
{
    private function parseDocComment(string $comment): array
    {
        $result = [];

        foreach (\preg_split('#[\r\n]+#', $comment, -1, \PREG_SPLIT_NO_EMPTY) as $line) {
            if (\preg_match('#\s\*\s*@([a-z0-9\-_]+)\s*(.*)#i', $line, $match)) {
                $result[\strtolower($match[1])][] = $match[2] ?? '';
            }
        }

        return $result;
    }

    protected function executeJSONResponder(callable $callback)
    {
        $result = ['success' => true];

        try {
            $result = \array_merge($result, (array)$callback());
        } catch (\Exception $e) {
            http_response_line_from_exception($e);

            $result['success'] = false;
            $result['message'] = $e->getMessage();
        } finally {
            if (!isset($headers['content-type'])) {
                \header('Content-Type: application/json');
            }

            foreach ($this->headers as [$name, $value]) {
                \header("{$name}: {$value}");
            }

            echo \json_encode($result);
        }
    }

    private function commentFlagIsEnabled(array $values): bool
    {
        return !\in_array(\strtolower($values[0]), ['no', 'off', 'false']);
    }

    private function processDocComment(string $comment): void
    {
        $vars = [];

        foreach (\preg_split('#[\r\n]+#', $comment, -1, \PREG_SPLIT_NO_EMPTY) as $line) {
            if (\preg_match('#\s\*\s*@([a-z0-9\-_]+)\s*(.*)#i', $line, $match)) {
                $vars[\strtolower($match[1])][] = \trim($match[2]) ?? '';
            }
        }

        if (isset($vars['jsonresponder'])) {
            $this->responderType = $this->commentFlagIsEnabled($vars['jsonresponder'])
                ? self::RESPONDER_JSON
                : self::RESPONDER_NONE;
        }

        if (isset($vars['extravars'])) {
            $this->useExtraVars = $this->commentFlagIsEnabled($vars['extravars']);
        }

        foreach ($vars['header'] ?? [] as $header) {
            if (!\preg_match('/^(\S+)\s+(\S.*)$/', $header, $parts)) {
                continue;
            }

            $this->headers[\strtolower($parts[1])] = [$parts[1], $parts[2]];
        }
    }

    private const RESPONDER_NONE = 0;
    private const RESPONDER_JSON = 1;

    private $responderType = self::RESPONDER_NONE;
    private $useExtraVars = false;
    private $headers = [];

    public function __call(string $name, array $arguments)
    {
        try {
            $object = new \ReflectionObject($this);

            if (false !== $comment = $object->getDocComment()) {
                $this->processDocComment($comment);
            }

            $method = $object->getMethod($name);

            if (false !== $comment = $method->getDocComment()) {
                $this->processDocComment($comment);
            }

            if ($this->responderType === self::RESPONDER_NONE) {
                throw new InvalidRouteException('Invalid route target (no responder set): ' . \get_class($this) . '::' . $name);
            }

            if (!$this->useExtraVars) {
                $arguments = [\array_pop($arguments)];
            }

            $this->executeJSONResponder(function() use($method, $arguments) {
                return $method->getClosure($this)(...$arguments);
            });
        } catch (\ReflectionException $e) {
            throw new InvalidRouteException("Invalid route target ({$e->getMessage()}): " . \get_class($this) . '::' . $name);
        }
    }
}
