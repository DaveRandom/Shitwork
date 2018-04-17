<?php declare(strict_types = 1);

namespace Shitwork\Routing;

use Auryn\Injector;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Shitwork\Exceptions\MethodNotAllowedException;
use Shitwork\Exceptions\NotFoundException;
use Shitwork\Request;
use Shitwork\Routing\Routes\Route;
use Shitwork\Session;

final class Router
{
    private $injector;
    private $session;
    private $dispatcher;
    private $routes;

    /**
     * Router constructor.
     * @param Injector $injector
     * @param Session $session
     * @param Route[] $routes
     * @uses collectRoutes
     */
    public function __construct(Injector $injector, Session $session, array $routes = [])
    {
        $this->injector = $injector;
        $this->session = $session;
        $this->routes = \array_values($routes);

        $this->dispatcher = \FastRoute\simpleDispatcher((new \ReflectionObject($this))->getMethod('collectRoutes')->getClosure($this));
    }

    /**
     * @throws InvalidRouteException
     */
    private function collectRoutes(RouteCollector $collector): void
    {
        foreach ($this->routes as $i => $route) {
            if (!$route instanceof Route) {
                throw new InvalidRouteException('Invalid', 'invalid', 'Entry at index ' . $i . ' is not an instance of ' . Route::class);
            }

            $collector->addRoute($route->getHttpMethod(), $route->getUriPattern(), $route);
        }
    }

    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @throws MethodNotAllowedException
     * @throws InvalidRouteException
     * @throws NotFoundException
     */
    public function dispatchRequest(Request $request): RouteTarget
    {
        $path = \rawurldecode($request->getURIPath());
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundException('Undefined route: ' . $path);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException('Invalid request method for route ' . $path . ': ' . $request->getMethod());
        }

        /** @var Route $route */
        /** @var array $vars */
        list(, $route, $vars) = $routeInfo;

        return $route->getTarget($this->injector, $vars);
    }
}
