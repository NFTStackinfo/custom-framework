<?php

namespace Db\Model\Field;

class CharField extends FieldAbstract {
    protected static $type = 'VARCHAR';

    protected $length = 256;
}