<?php

namespace Db\Model\Field;

class DecimalField extends FieldAbstract {
    protected static $type = 'DECIMAL';

    protected $length = 15.8;
}