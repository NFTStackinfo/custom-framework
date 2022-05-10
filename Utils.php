<?php

use Core\Route\Route;

use Models\PermissionModel;
use Middlewares\Middlewares;

/**
 * Get parameter from $request
 * Shorthand for $request['data']->get(...)
 *
 * @param array $request
 * @param string $name
 * @param array $filters
 *
 * @return mixed
 */
function getParam(array $request, string $name, array $filters = []) {
    return $request['data']->get($name, $filters);
}

/**
 * Get several parameters from $request
 *
 * @param array $request
 * @param array $params
 *
 * @return array
 */
function getParams(array $request, array $params) {
    $return_params = [];
    foreach ($params as $param_name => $param_filters) {
        $return_params[$param_name] = getParam($request, $param_name, $param_filters);
    }
    return $return_params;
}

/**
 * @param array $request
 *
 * @return mixed|null
 */
function getUser(array $request) {
    return isset($request['user']) ? $request['user'] : null;
}

/**
 * Shorthand for creating CRUD routes with access permissions
 *
 * @param string $model
 * @param string $path
 * @param string $controller
 *
 * @return Route[]
 * @throws Exception
 */
function CRUDRoutes(string $model, string $path, string $controller): array {
    /**
     * @var $routes Route[]
     */
    $routes = Route::crud($path, $controller);

    return [
        $routes['create']->middleware(Middlewares::Permission,
                                      PermissionModel::permissionName($model, 'create')),

        $routes['read']->middleware(Middlewares::Permission,
                                    PermissionModel::permissionName($model, 'read')),

        $routes['list']->middleware(Middlewares::Permission,
                                    PermissionModel::permissionName($model, 'list')),

        $routes['update']->middleware(Middlewares::Permission,
                                      PermissionModel::permissionName($model, 'update')),

        $routes['delete']->middleware(Middlewares::Permission,
                                      PermissionModel::permissionName($model, 'delete')),
    ];
}