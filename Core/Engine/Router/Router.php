<?php

namespace Engine\Router;

use Engine\Router\Exception\DuplicateRouteException;
use Engine\Router\Exception\InvalidRouteException;
use Engine\Router\Exception\RouteNotFoundException;

use Core\Route\Route;
use Core\Route\Methods;

class Router {
    private $routes = [];

    private $matchTypes = [
        '%n' => '([0-9]+)',
        '%s' => '([A-Za-z0-9_]+)',
        '%h' => '([A-Fa-f0-9]+)',
    ];

    /**
     * @param Route $route
     *
     * @return Router
     * @throws DuplicateRouteException
     * @throws InvalidRouteException
     */
    public function setRoute(Route $route): Router {
        if (!$route instanceof Route) {
            throw new InvalidRouteException();
        }

        if ($this->hasRoute($route)) {
            throw new DuplicateRouteException();
        }

        $this->routes[] = $route;

        return $this;
    }

    /**
     * @param Route $route
     *
     * @return bool
     */
    public function hasRoute(Route $route): bool {
        foreach ($this->routes as $iter_route) {
            if (Route::equalByPath($iter_route, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $route_match
     *
     * @return mixed
     * @throws RouteNotFoundException
     */
    public function getRoute(string $method, string $path, &$route_match = null) {
        $path = trim($path);
        $path = rtrim($path, '/');

        foreach ($this->routes as $route) {
            if ($route->getMethod() != $method && $method != Methods::OPTIONS) {
                continue;
            }

            if (!$this->match($route->getPath(), $path)) {
                continue;
            }

            if ($route_match !== null) {
                $route_match = $this->getParameters($route->getPath(), $path);
            }

            return $route;
        }

        throw new RouteNotFoundException();
    }


    // TODO: optimize matching
    /**
     * @param string $route
     * @param string $path
     *
     * @return bool
     */
    private function match(string $route, string $path): bool {
        if ($route == $path) {
            return true;
        }

        foreach ($this->matchTypes as $pattern => $regex) {
            $route = preg_replace("/{$pattern}\:[A-Za-z]+/", $regex, $route);
        }

        $route = str_replace('/', '\/', $route);

        if (preg_match("/^{$route}$/", $path) === 1) {
            return true;
        }

        return false;
    }

    /**
     * @param string $route
     * @param string $path
     *
     * @return array
     */
    private function getParameters(string $route, string $path): array {
        $parameters = [];
        $matched_parts = array_combine(explode('/', $route), explode('/', $path));

        foreach ($matched_parts as $route_part => $path_part) {
            $param_match = [];
            if (preg_match("/\%[a-z]+\:([A-Za-z]+)/", $route_part, $param_match)) {
                $param_name = $param_match[1];
                $parameters[$param_name] = $path_part;
            }
        }

        return $parameters;
    }
}