<?php

namespace Db\Model\Field;

use Db\Model\Field\Exception\InvalidValueException;

class IntField extends FieldAbstract {
    protected static $type = 'INT';

    protected $length = 11;

    public function value($value) {
        $value = parent::value($value);

        if (!is_numeric($value)) {
            throw new InvalidValueException();
        }

        $value = intval($value);

        return $value;
    }
}