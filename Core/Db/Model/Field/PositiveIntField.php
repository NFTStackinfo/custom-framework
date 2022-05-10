<?php

namespace Db\Model\Field;

use Db\Model\Field\Exception\InvalidValueException;

class PositiveIntField extends IntField {
    protected $is_unsigned = true;

    public function value($value) {
        $value = parent::value($value);

        if ($value < 0) {
            throw new InvalidValueException();
        }

        return $value;
    }
}
