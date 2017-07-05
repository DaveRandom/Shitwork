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
                $result[\strtolower($match[1])] = $match[2] ?? '';
            }
        }

        return $result;
    }

    protected function executeJSONResponder(callable $callback)
    {
        $result = ['success' => true];

        try {
            $result = array_merge($result, (array)$callback());
        } catch(\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
        } finally {
            header('Content-Type: application/json');
            echo json_encode($result);
        }
    }

    public function __call(string $name, array $arguments)
    {
        try {
            $method = (new \ReflectionObject($this))->getMethod($name);

            if (false === $comment = $method->getDocComment()) {
                throw new InvalidRouteException('Invalid route target: ' . self::class . '::' . $name);
            }

            $comment = $this->parseDocComment($comment);

            if (!isset($comment['jsonresponder'])) {
                throw new InvalidRouteException('Invalid route target: ' . self::class . '::' . $name);
            }

            $closure = $method->getClosure($this);

            $this->executeJSONResponder(function() use($closure, $arguments) {
                return $closure(...$arguments);
            });
        } catch (\ReflectionException $e) {
            throw new InvalidRouteException('Invalid route target: ' . self::class . '::' . $name);
        }
    }
}
