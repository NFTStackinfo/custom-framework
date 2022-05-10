<?php

namespace Core\Response;

class HttpResponse extends ResponseAbstract {
    protected static function setHeaders() {
        header('Content-Type: text/html; charset=utf-8');
    }

    protected static function process($response = null) {
        if ($response === null) {
            $response = '';
        } elseif (is_array($response) || is_object($response)) {
            $response = print_r($response, true);
        }

        return $response;
    }
}