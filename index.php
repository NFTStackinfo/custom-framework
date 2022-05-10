<?php

use Engine\Router\Exception\RouteNotFoundException;
use Engine\Parser\Exception\InvalidParamException;
use Engine\Debugger\Traceback;

use Core\Response\HttpResponse;
use Core\Middleware\Exception\CORSForbiddenOriginException;
use Core\Middleware\Exception\CORSForbiddenMethodException;

use Db\Db;
use Db\Exception\DbAdapterException;

use Middlewares\Middlewares;
use Middlewares\Exception\AuthRequiredException;
use Middlewares\Exception\ForbiddenUserException;
use Middlewares\Exception\InvalidCredentialsException;

require_once 'include.php';
require_once 'Utils.php';

// Include routes
require_once $root . '/Api/Routes.php';
$kernel->registerRoutes($api_routes);

// Register middlewares
$kernel->registerMiddleware(Middlewares::AuthToken)
    ->registerMiddleware(Middlewares::Permission);

// Process request and handle exceptions
try {
    $kernel->request();
} catch (CORSForbiddenOriginException $e) {
    HttpResponse::error('403: Forbidden: Forbidden origin', 403);
} catch (CORSForbiddenMethodException $e) {
    HttpResponse::error('403: Forbidden: Forbidden method', 403);
} catch (ForbiddenUserException $e) {
    HttpResponse::error('403: Forbidden: Can not access resource', 403);
}  catch (InvalidCredentialsException $e) {
    HttpResponse::error('403: Forbidden: Invalid credentials', 403);
} catch (RouteNotFoundException $e) {
    HttpResponse::error('404: Not found', 404);
} catch (AuthRequiredException $e) {
    HttpResponse::error('403: Forbidden: Auth required', 403);
} catch (InvalidParamException $e) {
    HttpResponse::error('ParamError: ' . $e->getMessage());
} catch (DbAdapterException $e) {
    if (KERNEL_CONFIG['debug']) {
        $pretty = Traceback::pretty(Db::getError(), Traceback::stringifyException($e));
        HttpResponse::response($pretty);
    } else {
        HttpResponse::error();
    }
} catch (Exception $e) {
    if (KERNEL_CONFIG['debug']) {
        $pretty = Traceback::pretty(Traceback::stringifyException($e));
        HttpResponse::response($pretty);
    } else {
        HttpResponse::error();
    }
}

exit();
