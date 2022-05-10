<?php

namespace Db\Model\Field;

class RandomTokenField extends AutofillField {
    protected static $type = 'VARCHAR';

    protected $length = 32;

    public function fill($meta = null) {
        $token_rand = mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        $token_time = round(microtime(true) * 1000);

        $token = substr(hash('sha256', $token_time . $token_rand), 0, $this->length - 3);

        // Divide 32 character string into 10:6:6:- parts
        $token_dashed = (
            substr($token, 0, 10) . '-' .
            substr($token, 10, 6) . '-' .
            substr($token, 16, 6) . '-' .
            substr($token, 22)
        );

        return $token_dashed;
    }
}