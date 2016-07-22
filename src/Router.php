<?php declare(strict_types = 1);

namespace Shitwork;

use Auryn\Injector;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class InvalidRouteException extends \Exception {}
class RouteNotFoundException extends \Exception {}
class RouteMethodNotAllowedException extends \Exception {}

class Router
{
    private $injector;
    private $session;
    private $dispatcher;

    public function __construct(Injector $injector, Session $session, array $routes)
    {
        $this->injector = $injector;
        $this->session = $session;

        $this->dispatcher = simpleDispatcher(function(RouteCollector $r) use($routes) {
            foreach ($routes as $i => $route) {
                if (!isset($route['method'], $route['pattern'])) {
                    throw new InvalidRouteException('Invalid route at index ' . $i . ': method and pattern required');
                }

                if (isset($route['target_builder'])) {
                    $r->addRoute($route['method'], $route['pattern'], $route['target_builder']);
                } else if (isset($route['target_class'], $route['target_method'])) {
                    $r->addRoute($route['method'], $route['pattern'], [
                        'class' => $route['target_class'],
                        'method' => $route['target_method'],
                    ]);
                } else {
                    throw new InvalidRouteException('Invalid route at index ' . $i . ': no valid target');
                }
            }
        });
    }

    public static function methodCallTargetBuilder($objectOrClassName, string $varName = 'method')
    {
        return function(array $vars, Injector $injector) use($objectOrClassName, $varName) {
            $target = [
                is_object($objectOrClassName) ? $objectOrClassName : $injector->make($objectOrClassName),
                strtr($vars[$varName], ['-' => ''])
            ];

            if (!is_callable($target)) {
                throw new \Exception('Unknown endpoint: ' . $vars[$varName]);
            }

            return $target;
        };
    }

    public function route(Request $request): array
    {
        $path = rawurldecode($request->getURIPath());
        $route = $this->dispatcher->dispatch($request->getMethod(), $path);

        switch ($route[0]) {
            case Dispatcher::NOT_FOUND:
                throw new RouteNotFoundException('Undefined route: ' . $path, 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new RouteMethodNotAllowedException('Invalid request method for route ' . $path . ': ' . $request->getMethod(), 405);
        }

        list(, $routeInfo, $vars) = $route;

        $target = is_callable($routeInfo)
            ? $routeInfo($vars, $this->injector, $this->session, $request)
            : [$this->injector->make($routeInfo['class']), $routeInfo['method']];

        return [$target, $vars];
    }
}
