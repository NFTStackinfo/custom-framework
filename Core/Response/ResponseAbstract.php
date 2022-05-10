<?php

namespace Core\Response;

abstract class ResponseAbstract {
    abstract protected static function setHeaders();

    abstract protected static function process($response);

    public static function response($response = null) {
        static::setHeaders();

        $processed_response = static::process($response);

        exit($processed_response);
    }

    public static function ok($response = null) {
        http_response_code(200);
        static::response($response);
    }

    public static function error($response = null, $code = 400) {
        http_response_code($code);
        static::response($response);
    }
}