<?php

use Core\Route\Route;

use Core\Middleware\DefaultMiddlewares;
use Middlewares\Middlewares;

$api_routes = (
    Route::groupMiddleware(DefaultMiddlewares::CORS,
    Route::group('/api',
        Route::group('/v1',
            // Module `Test`
            Route::get('/ping', 'Api\Test@ping'),
            Route::get('/test', 'Api\Test@test'),
        ),

        // CRUD models endpoints
        Route::groupMiddleware(Middlewares::AuthToken,
        Route::group('/model',
            CRUDRoutes('user', '/user', 'Api\CRUD\User@UserCRUD'),
        ))
    ))
);