<?php

namespace Core\Route;

use Core\Route\Exception\InvalidPathException;
use Core\Route\Exception\UnknownMethodException;

class Route {
    private $base_path = '';

    private $path = '';

    private $method = '';

    private $controller = '';

    private $middlewares = [];

    private $availableMethods = [Methods::GET, Methods::POST, Methods::PUT, Methods::DELETE];

    /**
     * @param string $set_path
     * @param $group_items
     *
     * @return array
     */
    public static function group(string $set_path, ...$group_items): array {
        foreach ($group_items as $item_key => $item) {
            if (is_array($item)) {
                foreach ($item as $route) {
                    $route->prependBasePath($set_path);
                }
            } elseif ($item instanceof Route) {
                $item->prependBasePath($set_path);
                $group_items[$item_key] = [$item];
            }
        }

        return array_merge(...$group_items);
    }

    /**
     * @param $middleware
     * @param mixed ...$group_items
     *
     * @return array
     */
    public static function groupMiddleware($middleware, ...$group_items): array {
        if (!is_array($middleware)) {
            $middleware = [
                $middleware => [],
            ];
        }

        foreach ($middleware as $middleware_name => $middleware_args) {
            if (!is_array($middleware_args)) {
                $middleware_args = [$middleware_args];
            }

            foreach ($group_items as $item_key => $item) {
                if (is_array($item)) {
                    foreach ($item as $route) {
                        $route->setMiddleware($middleware_name, $middleware_args);
                    }
                } elseif ($item instanceof Route) {
                    $item->setMiddleware($middleware_name, $middleware_args);
                    $group_items[$item_key] = [$item];
                }
            }
        }

        return array_merge(...$group_items);
    }

    /**
     * @param Route $A
     * @param Route $B
     *
     * @return bool
     */
    public static function equalByPath(Route $A, Route $B): bool {
        if ($A->getPath() !== $B->getPath()) {
            return false;
        }

        if ($A->getMethod() !== $B->getMethod()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $path
     * @param string $controller
     *
     * @return Route
     * @throws InvalidPathException
     * @throws UnknownMethodException
     */
    public static function get(string $path, string $controller): Route {
        return new Route($path, Methods::GET, $controller);
    }

    /**
     * @param string $path
     * @param string $controller
     *
     * @return Route
     * @throws InvalidPathException
     * @throws UnknownMethodException
     */
    public static function post(string $path, string $controller): Route {
        return new Route($path, Methods::POST, $controller);
    }

    /**
     * @param string $path
     * @param string $controller
     *
     * @return Route
     * @throws InvalidPathException
     * @throws UnknownMethodException
     */
    public static function put(string $path, string $controller): Route {
        return new Route($path, Methods::PUT, $controller);
    }

    /**
     * @param string $path
     * @param string $controller
     *
     * @return Route
     * @throws InvalidPathException
     * @throws UnknownMethodException
     */
    public static function delete(string $path, string $controller): Route {
        return new Route($path, Methods::DELETE, $controller);
    }

    /**
     * Shorthand for model create, read, update, delete
     *
     * @param string $path
     * @param string $controller
     *
     * @return array
     * @throws InvalidPathException
     * @throws UnknownMethodException
     */
    public static function crud(string $path, string $controller): array {
        return [
            'create' => Route::put($path, $controller . '::create'),
            'read' => Route::get($path . '/%n:id', $controller . '::read'),
            'list' =>  Route::get($path, $controller . '::list'),
            'update' => Route::post($path . '/%n:id', $controller . '::update'),
            'delete' => Route::delete($path . '/%n:id', $controller . '::delete'),
        ];
    }

    /**
     * Route constructor.
     *
     * @param string $path
     * @param string $method
     * @param string $controller
     *
     * @throws InvalidPathException
     * @throws UnknownMethodException
     */
    function __construct(string $path, string $method, string $controller) {
        $path = trim($path);
        if ($path != '/') {
            $path = rtrim($path, '/');
        }

        if (!$path) {
            throw new InvalidPathException();
        }

        $method = strtoupper(trim($method));
        if (!in_array($method, $this->availableMethods)) {
            throw new UnknownMethodException();
        }

        $this->path = rtrim($path, '/');
        $this->method = $method;
        $this->controller = $controller;
    }

    /**
     * @param string $path
     */
    public function prependBasePath(string $path) {
        $this->base_path = $path . $this->base_path;
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->base_path . $this->path;
    }

    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getController(): string {
        return $this->controller;
    }

    /**
     * @param string $middleware
     * @param array  $args
     *
     * @return $this
     */
    public function setMiddleware(string $middleware, array $args) {
        if (!$this->hasMiddleware($middleware)) {
            $this->middlewares = array_merge([$middleware => $args], $this->middlewares);
        }
        return $this;
    }

    /**
     * @param string $middleware
     *
     * @return bool
     */
    public function hasMiddleware(string $middleware): bool {
        if (in_array($middleware, array_keys($this->middlewares))) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array {
        return $this->middlewares;
    }

    /**
     * @param string $middleware
     * @param mixed  $args
     *
     * @return Route
     */
    public function middleware(string $middleware, $args = []) {
        if (!is_array($args)) {
            $args = [$args];
        }

        return $this->setMiddleware($middleware, $args);
    }
}