<?php

namespace Db\Model\Field;

use Db\Model\Field\Exception\InvalidValueException;

class EmailField extends CharField {
    protected $length = 128;

    public function value($value) {
        $value = parent::value($value);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidValueException();
        }

        return $value;
    }
}