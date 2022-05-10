<?php

namespace Engine;

use Engine\Exception\ControllerInvalidNameException;
use Engine\Exception\ControllerNotFoundException;
use Engine\Loader\Loader;
use Engine\Loader\Exception\NotFoundException as LoaderNotFoundException;
use Engine\Router\Router;
use Engine\Router\Exception\DuplicateRouteException;
use Engine\Router\Exception\InvalidRouteException;
use Engine\Router\Exception\RouteNotFoundException;
use Engine\Parser\Parser;

use Db\Db;
use Db\Exception;

use Core\Middleware\MiddlewareInterface;
use Core\Middleware\Exception\NotFoundException as MiddlewareNotFoundException;
use Core\Middleware\Exception\WrongTypeException;

class Kernel {
    private $app_path = '';

    private $router = null;

    private $loader = null;

    private $middlewares = [];

    /**
     * Kernel constructor.
     *
     * @param $app_path
     * @param $config_file
     *
     * @throws Exception\DbAdapterException
     */
    function __construct($app_path, $config_file) {
        $this->app_path = $app_path;

        // Load shared config
        require_once $app_path . '/' . $config_file;
        $config = $KERNEL_CONFIG;

        // Load local config if exists and rewrite
        if (file_exists($app_path . '/' . 'loc.' . $config_file)) {
            require_once $app_path . '/' . 'loc.' . $config_file;
            $config = array_merge($config, $KERNEL_CONFIG);
        }

        // Set config
        $config['root'] = $app_path;
        define('KERNEL_CONFIG', $config);

        // Error reporting
        if (KERNEL_CONFIG['debug']) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            error_reporting(0);
        }

        // Init Loader
        require_once 'Loader/Loader.php';
        $this->loader = new Loader();

        // Set prefixes
        if (isset(KERNEL_CONFIG['autoloader']['prefixes'])) {
            foreach (KERNEL_CONFIG['autoloader']['prefixes'] as $name => $prefix) {
                $this->loader->addNamespace($name, $app_path . $prefix);
            }
        }

        // Register loader
        $this->loader->register();

        // Set db connection
        $db_params = KERNEL_CONFIG['db'];
        Db::setConnection($db_params['host'],
            $db_params['user']['name'],
            $db_params['user']['password'],
            $db_params['name']);

        // Init router
        $this->router = new Router();
    }

    /**
     * @return Router
     */
    public function getRouter(): Router {
        return $this->router;
    }

    /**
     * @return Loader
     */
    public function getLoader(): Loader {
        return $this->loader;
    }

    /**
     * @param array $routes
     *
     * @return $this
     * @throws DuplicateRouteException
     * @throws InvalidRouteException
     */
    public function registerRoutes(array $routes): Kernel {
        foreach ($routes as $route) {
            $this->router->setRoute($route);
        }
        return $this;
    }

    /**
     * @param $middleware
     *
     * @return $this
     */
    public function registerMiddleware(string $middleware): Kernel {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * @throws ControllerInvalidNameException
     * @throws ControllerNotFoundException
     * @throws MiddlewareNotFoundException
     * @throws RouteNotFoundException
     * @throws WrongTypeException
     */
    public function request() {
        $request_method = $_SERVER['REQUEST_METHOD'];
        $request_path = parse_url($_SERVER['REQUEST_URI'])['path'];

        $request = [];

        $route_parameters = [];
        $route = $this->router->getRoute($request_method, $request_path, $route_parameters);

        $this->processParser($request, $route_parameters);
        $this->processMiddlewares($route->getMiddlewares(), $request);

        $this->processController($route->getController())($request);
    }

    /**
     * @param array $request
     * @param array $url_parameters
     */
    private function processParser(array &$request, array $url_parameters = []) {
        $request['data'] = new Parser($url_parameters);
    }

    /**
     * @param array $middlewares
     * @param $request
     *
     * @throws MiddlewareNotFoundException
     * @throws WrongTypeException
     */
    private function processMiddlewares(array $middlewares, &$request) {
        foreach ($middlewares as $middleware => $middleware_args) {
            if (in_array($middleware, $this->middlewares)) {
                $namespace = 'Middlewares\\' . $middleware;
            } else {
                $namespace = 'Core\Middleware\\' . $middleware;
            }

            try {
                $middlewareInst = new $namespace();
            } catch (LoaderNotFoundException $e) {
                throw new MiddlewareNotFoundException();
            }

            if (!$middlewareInst instanceof MiddlewareInterface) {
                throw new WrongTypeException();
            }

            $middlewareInst->process($request, ...$middleware_args);
        }
    }

    /**
     * @param string $controller
     *
     * @return string
     * @throws ControllerInvalidNameException
     * @throws ControllerNotFoundException
     */
    private function processController(string $controller) {
        if (strpos($controller, '@') === false) {
            throw new ControllerInvalidNameException();
        }
        list($namespace, $callable) = explode('@', $controller);

        $file_path = $this->app_path . '/';
        $file_path .= str_replace('\\', '/', $namespace);

        if (is_dir($file_path)) {
            $file_path .= '/Controller.php';
        } elseif (is_file($file_path . '.php')) {
            $file_path .= '.php';
        }

        if (!file_exists($file_path)) {
            throw new ControllerNotFoundException();
        }
        require_once $file_path;

        $callable = $namespace . '\\' . $callable;

        if (!is_callable($callable)) {
            throw new ControllerNotFoundException();
        }

        return $callable;
    }
}