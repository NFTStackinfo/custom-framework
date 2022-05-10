<?php

namespace Modules;

use Exception;

use Db\Where;

class ProfileModule {
    /**
     * @var array Permissions cache
     */
    private static $permissions_tmp = [];

    /**
     * Signs up new user
     *
     * @param string $first_name User first name
     * @param string $last_name  User last name
     *
     * @return int
     * @throws Exception
     */
    public static function signUp(string $first_name, string $last_name): int {
        // sign up

        return 1;
    }

}