<?php

namespace Api\Profile;

use Core\Response\JsonResponse;

use Serializers\ProfileSerializer;

class Profile {
    public static function signUp($request) {
        /**
         * @var string $first_name  User first name
         * @var string $last_name   User last name
         */
        $params = getParams($request, [
            'first_name' => ['required', 'maxLen' => 256],
            'last_name' => ['required', 'maxLen' => 256],
        ]);
        extract($params);

        // sign up

        JsonResponse::ok();
    }

    public static function signIn($request) {
        /**
         * @var string $login       User login
         * @var string $password    User password
         */
        $params = getParams($request, [
            'login' => ['required'],
            'password' => ['required'],
        ]);
        extract($params);

        // sign in

        JsonResponse::ok();
    }

    public static function retrieve($request) {
        $user = getUser($request);

        $user = ProfileSerializer::detail($user);

        JsonResponse::ok($user);
    }
}