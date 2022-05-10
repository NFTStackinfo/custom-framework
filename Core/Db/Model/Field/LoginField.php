<?php

namespace Db\Model\Field;

use Db\Model\Field\Exception\InvalidValueException;

class LoginField extends CharField {
    protected $length = 128;

    public function value($value) {
        $value = parent::value($value);

        if (!preg_match("/^[A-Za-z0-9_]{3,{$this->length}}$/", $value)) {
            throw new InvalidValueException();
        }

        return $value;
    }
}