<?php

namespace Core\Services\Redis;

use Redis;

class RedisAdapter {
    /**
     * @var Redis
     */
    private static $inst;

    public static function shared() {
        if (self::$inst === null) {
            self::$inst = new Redis();
            self::$inst->connect('localhost', 6379);
        }

        return self::$inst;
    }

    public function __call($method, $args) {
        return self::$inst->$method($args);
    }
}