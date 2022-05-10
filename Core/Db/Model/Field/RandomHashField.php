<?php

namespace Db\Model\Field;

class RandomHashField extends AutofillField {
    protected static $type = 'VARCHAR';

    protected $length = 32;

    public function fill($meta = null) {
        $hash_meta = $meta && is_string($meta) ? (string) $meta : '';
        $hash_rand = mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        $hash_time = round(microtime(true) * 1000);

        $hash = substr(hash('sha256', $hash_time . $hash_meta . $hash_rand), 0, $this->length);

        return $hash;
    }
}
