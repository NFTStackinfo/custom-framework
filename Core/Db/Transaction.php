<?php

namespace Db;

use Closure;
use Exception;

class Transaction {
    public static function wrap(Closure $func) {
        $conn = Db::conn();

        $conn->begin_transaction();

        try {
            $func();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $conn->commit();
    }
}