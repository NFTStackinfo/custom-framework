<?php

namespace Middlewares;

use Exception;

use Core\Middleware\MiddlewareInterface;

use Db\Model\Exception\ModelNotFoundException;
use Db\Where;

use Middlewares\Exception\AuthRequiredException;
use Middlewares\Exception\InvalidCredentialsException;
use Models\UserModel;
//use Models\AppTokenModel;

class AuthTokenMiddleware implements MiddlewareInterface {
    public function process(&$request) {
        if (!isset($_SERVER['HTTP_X_TOKEN'])) {
            throw new AuthRequiredException();
        }

        $token_str = trim($_SERVER['HTTP_X_TOKEN']);

        try {
            //$token = AppTokenModel::select(Where::equal('token', $token_str))->first();
            //$user = UserModel::get($token->owner_id);
        } catch (Exception $e) {
            throw new InvalidCredentialsException();
        }

        //$request['user'] = $user;
    }
}
