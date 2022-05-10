<?php

namespace Db;

use Db\Exception\DbAdapterException;
use Db\Exception\InvalidSelectQueryException;
use Db\Exception\InvalidWhereOperatorException;

class QueryBuilder {
    /**
     * @param string $table_name
     *
     * @return QueryBuilder
     */
    public static function query(string $table_name) {
        $inst = new self();

        $inst->table_name = $table_name;

        return $inst;
    }

    /**
     * @var string
     */
    private $table_name;

    /**
     * @var string
     */
    private $for_update = false;

    /**
     * @var array
     */
    private $builder = [];

    /**
     * SelectBuilder constructor.
     */
    function __construct() {
        $this->builder = [
            'columns' => null,
            'where' => null,
            'group_by' => null,
            'order_by' => null,
            'limit' => null,
            'offset' => null,
        ];
    }

    /**
     * @param array $columns
     *
     * @return QueryBuilder
     */
    public function columns(array $columns): QueryBuilder {
        $add_columns = [];
        foreach ($columns as $column_key => $column_annotation) {
            if (is_string($column_key)) {
                $add_columns[] = "{$column_key} AS {$column_annotation}";
            } else {
                $add_columns[] = $column_annotation;
            }
        }

        if ($this->builder['columns'] === null) {
            $this->builder['columns'] = $add_columns;
        } else {
            $this->builder['columns'] = array_merge($this->builder['columns'], $add_columns);
        }

        return $this;
    }

    /**
     * @param Where $where
     *
     * @return QueryBuilder
     */
    public function where(Where $where): QueryBuilder {
        $this->builder['where'] = $where;

        return $this;
    }

    /**
     * @param array|string $group_by
     *
     * @return QueryBuilder
     * for select only
     */
    public function groupBy($group_by): QueryBuilder {
        if (!is_array($group_by)) {
            $group_by = [$group_by];
        }

        $this->builder['group_by'] = $group_by;

        return $this;
    }

    /**
     * @param array $order_by
     *
     * @return QueryBuilder
     * for select only
     */
    public function orderBy(array $order_by): QueryBuilder {
        $this->builder['order_by'] = $order_by;

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return QueryBuilder
     * for select only
     */
    public function limit(int $limit): QueryBuilder {
        $this->builder['limit'] = $limit;

        return $this;

    }

    /**
     * @param int $offset
     *
     * @return QueryBuilder
     * for select only
     */
    public function offset(int $offset): QueryBuilder {
        $this->builder['offset'] = $offset;

        return $this;
    }

    /**
     * @param bool $for_update
     *
     * @return QueryBuilder
     * for get only
     */
    public function forUpdate($for_update = true) {
        $this->for_update = $for_update;

        return $this;
    }

    /**
     * @return array
     * @throws DbAdapterException
     * @throws InvalidSelectQueryException
     * @throws InvalidWhereOperatorException
     */
    public function select() {
        $table_name = $this->table_name;

        return Db::select($table_name,
                          $this->builder['columns'],
                          $this->builder['where'],
                          $this->builder['group_by'],
                          $this->builder['order_by'],
                          $this->builder['limit'],
                          $this->builder['offset']);
    }

    /**
     * @return array
     * @throws DbAdapterException
     * @throws InvalidSelectQueryException
     * @throws InvalidWhereOperatorException
     */
    public function get() {
        $table_name = $this->table_name;

        return Db::get($table_name, $this->builder['columns'], $this->builder['where'], $this->for_update);
    }
}
