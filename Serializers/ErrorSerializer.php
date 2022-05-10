<?php

namespace Serializers;

class ErrorSerializer {
    public static function detail($code, $message) {
        return [
            'code' => $code,
            'message' => $message,
        ];
    }
}
