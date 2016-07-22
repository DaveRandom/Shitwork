<?php declare(strict_types = 1);

namespace Shitwork;

abstract class Controller
{
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
}
