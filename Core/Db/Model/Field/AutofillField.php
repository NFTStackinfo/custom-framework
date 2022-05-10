<?php

namespace Db\Model\Field;

abstract class AutofillField extends FieldAbstract {
    protected $is_null = false;

    abstract public function fill($meta = null);
}