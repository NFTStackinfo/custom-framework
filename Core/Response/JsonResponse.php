<?php

namespace Core\Response;

class JsonResponse extends ResponseAbstract {
    protected static function setHeaders() {
        header('Content-Type: application/json; charset=utf-8');
    }

    protected static function process($response = null) {
        if ($response === null) {
            $response = '{}';
        } elseif (is_array($response)) {
            $response = json_encode($response);
        } elseif (is_string($response) || is_numeric($response)) {
            $response = json_encode([
                'response' => $response,
            ]);
        }

        return $response;
    }
}