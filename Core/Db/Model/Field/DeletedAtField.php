<?php

namespace Db\Model\Field;

class DeletedAtField extends IntField {
    protected $length = 10;

    protected $is_unsigned = true;

    protected $is_null = true;
}
