<?php

namespace Db\Model\Field;

use Db\Model\Field\Exception\InvalidValueException;

abstract class FieldAbstract {
    /**
     * @var string
     */
    protected static $type;

    /**
     * @return string
     */
    public static function getType() {
        return strtoupper(static::$type);
    }

    /**
     * @return static
     */
    public static function init() {
        return new static();
    }

    /**
     * @var int
     */
    protected $length;

    /**
     * @var bool
     */
    protected $is_unsigned = false;

    /**
     * @var bool
     */
    protected $is_null = false;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * Method value
     * Filters value and throws error in case of invalid data
     *
     * @param $value
     *
     * @return mixed
     * @throws InvalidValueException
     */
    public function value($value) {
        if ($this->is_null === false && $value === null) {
            throw new InvalidValueException();
        }

        return $value;
    }

    /**
     * @param int $length
     *
     * @return FieldAbstract
     */
    public function setLength(int $length): FieldAbstract {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isUnsigned(): bool {
        return $this->is_unsigned;
    }

    /**
     * @param bool $is_null
     *
     * @return FieldAbstract
     */
    public function setNull(bool $is_null): FieldAbstract {
        $this->is_null = $is_null;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNull(): bool {
        return $this->is_null;
    }

    /**
     * @return mixed
     */
    public function getDefault() {
        return $this->default;
    }

}