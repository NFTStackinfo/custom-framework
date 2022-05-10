<?php

namespace Db;

use Db\Exception\InvalidWhereOperatorException;

/**
 * Class Where
 * Implements `Where` condition in SQL queries
 *
 * @package Db
 */
class Where {
    private $logical_operation = null;

    private $filters;

    const LogicalAND = 'AND';
    const LogicalOR = 'OR';

    const OperatorEq = '=';
    const OperatorLower = '<';
    const OperatorLowerEq = '<=';
    const OperatorGreater = '>';
    const OperatorGreaterEq = '>=';
    const OperatorIN = 'IN';
    const OperatorIs = 'IS';
    const OperatorNot = 'NOT';
    const OperatorIsNot = 'IS NOT';
    const OperatorBetween = 'BETWEEN';
    const OperatorFindInSet = 'FIND_IN_SET';

    /**
     * @param string $logical_operation
     *
     * @return Where
     * @throws InvalidWhereOperatorException
     */
    public static function type(string $logical_operation): Where {
        if ($logical_operation != self::LogicalAND && $logical_operation != self::LogicalOR) {
            throw new InvalidWhereOperatorException();
        }

        $w = new Where();
        $w->logical_operation = $logical_operation;

        return $w;
    }

    /**
     * Shorthand for Where::type(AND)
     *
     * @return Where
     * @throws InvalidWhereOperatorException
     */
    public static function and(): Where {
        return self::type(self::LogicalAND);
    }

    /**
     * Shorthand for Where::type(OR)
     *
     * @return Where
     * @throws InvalidWhereOperatorException
     */
    public static function or(): Where {
        return self::type(self::LogicalOR);
    }

    /**
     * Shorthand for simple non logical Where based on equal operator
     *
     * @param string $field
     * @param $value
     *
     * @return Where
     * @throws InvalidWhereOperatorException
     */
    public static function equal(string $field, $value): Where {
        $w = new Where();

        $w->addFilter($field, Where::OperatorEq, $value);

        return $w;
    }

    /**
     * Shorthand for simple non logical Where based on IN operator
     *
     * @param string $field
     * @param $value
     *
     * @return Where
     * @throws InvalidWhereOperatorException
     */
    public static function in(string $field, $value): Where {
        $w = new Where();

        $w->addFilter($field, Where::OperatorIN, $value);

        return $w;
    }

    public static function find_in_set($field, $value): Where {
        $w = new Where();

        $w->addFilter($field, Where::OperatorFindInSet, $value);

        return $w;
    }

    /**
     * @param $method
     * @param $args
     *
     * @return $this
     * @throws InvalidWhereOperatorException
     */
    public function __call($method, $args) {
        if ($method === 'set' && count($args) === 3) {
            $this->addFilter((string)$args[0], (string)$args[1], $args[2]);
            return $this;
        } elseif ($method === 'set' && count($args) === 1) {
            $this->filters[] = $args[0];
            return $this;
        }

        return $this;
    }

    /**
     * @param string $field
     * @param string $operation
     * @param $value
     *
     * @throws InvalidWhereOperatorException
     */
    public function addFilter(string $field, string $operation, $value) {
        if (is_string($value)) {
            $value = "'" . Db::escape($value) . "'";
        } elseif (is_int($value) || is_float($value) || is_double($value)) {
            // pass
        } elseif (is_array($value)) {
            // pass
        } elseif ($value === null) {
            $value = 'NULL';
        } else {
            throw new InvalidWhereOperatorException();
        }

        switch ($operation) {
            case self::OperatorEq:
            case self::OperatorLower:
            case self::OperatorLowerEq:
            case self::OperatorGreater:
            case self::OperatorGreaterEq:
            case self::OperatorIs:
            case self::OperatorIsNot:
            case self::OperatorNot:
                $condition_query = "`{$field}` {$operation} {$value}";
                break;
            case self::OperatorBetween:
                if (count($value) != 2) {
                    throw new InvalidWhereOperatorException();
                }

                $condition_query = "(`{$field}` BETWEEN '{$value['from']}' AND '{$value['to']}')";
                break;
            case self::OperatorIN:
                if (!is_array($value)) {
                    throw new InvalidWhereOperatorException();
                }

                if (empty($value)) {
                    $condition_query = "0";
                } else {
                    $value = implode(',', array_map('intval', $value));
                    $condition_query = "`{$field}` IN({$value})";
                }
                break;
            case self::OperatorFindInSet:
                $condition_query = "FIND_IN_SET({$value}, {$field})";
                break;
            default:
                throw new InvalidWhereOperatorException();
        }

        $this->filters[] = $condition_query;
    }

    /**
     * @return string
     * @throws InvalidWhereOperatorException
     */
    public function build(): string {
        $built = '';

        if (empty($this->filters)) {
            throw new InvalidWhereOperatorException();
        }

        if ($this->logical_operation !== null) {
            $built_filters = array_map(function ($filter) {
                if ($filter instanceof Where) {
                    return $filter->build();
                } elseif (is_string($filter)) {
                    return $filter;
                } else {
                    throw new InvalidWhereOperatorException();
                }
            }, $this->filters);

            $built = implode(" {$this->logical_operation} ", $built_filters);
        } else {
            if (count($this->filters) > 1) {
                throw new InvalidWhereOperatorException();
            }

            $built = $this->filters[0];
        }

        return '( ' . $built . ' )';
    }
}
