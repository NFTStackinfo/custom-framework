<?php

namespace Core\Middleware;

interface MiddlewareInterface {
    public function process(&$request);
}