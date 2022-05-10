<?php

namespace Db;

use Exception;
use mysqli_result;
use mysqli;

use Db\Exception\DbAdapterException;
use Db\Exception\InvalidQueryException;
use Db\Exception\InvalidInsertQueryException;
use Db\Exception\InvalidSelectQueryException;
use Db\Exception\InvalidUpdateQueryException;
use Db\Exception\InvalidDeleteQueryException;
use Db\Exception\InvalidWhereOperatorException;

class Db {
    /**
     * @var mysqli
     */
    static private $conn;

    /**
     * @return mysqli
     */
    static public function conn() {
        return self::$conn;
    }

    /**
     * @param string $host
     * @param string $user_name
     * @param string $user_password
     * @param string $name
     *
     * @throws DbAdapterException
     */
    static public function setConnection(string $host, string $user_name, string $user_password, string $name) {
        $conn = @mysqli_connect($host, $user_name, $user_password, $name, 3306);
        if (!$conn) {
            throw new DbAdapterException();
        }

        $conn->set_charset('utf8');

        self::$conn = $conn;
    }

    /**
     * @return array
     */
    static public function getError(): array {
        $conn = self::conn();

        return [
            'error_msg' => $conn ? $conn->error : null,
            'error_number' => $conn ? $conn->errno : null,
        ];
    }

    /**
     * @param string $query
     *
     * @return bool|mysqli_result
     * @throws DbAdapterException
     */
    static public function query(string $query) {
        try {
            $res = self::conn()->query($query);
        } catch (Exception $e) {
            throw new DbAdapterException();
        };

        return $res;
    }

    /**
     * @param string $name
     * @param array $fields
     * @param bool $default_fields
     * @param bool $primary_field
     * @param string $engine
     *
     * @return bool
     * @throws DbAdapterException
     * @throws InvalidQueryException
     */
    static public function createTable(string $name, array $fields, bool $default_fields = true,
        bool $primary_field = true,string $engine = 'InnoDB'): bool {

        $fields_query = [];
        $primary_query = '';

        if ($primary_field) {
            $fields_query[] = 'id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT';
            $primary_query = ', PRIMARY KEY (id)';
        }

        if ($default_fields) {
            $fields_query[] = 'created_at_timestamp INT(10) UNSIGNED NOT NULL';
            $fields_query[] = 'updated_at_timestamp INT(10) UNSIGNED NOT NULL';
            $fields_query[] = 'deleted_at INT(10) UNSIGNED NULL DEFAULT NULL';
        }

        foreach ($fields as $field_name => $field) {
            $is_unsigned_query = '';
            $is_null_query = '';
            $default_query = '';

            if ($field->isUnsigned()) {
                $is_null_query = 'UNSIGNED';
            }

            if ($field->isNull()) {
                $is_null_query = 'NULL';
            } else {
                $is_null_query = 'NOT NULL';
            }

            if (($default = $field->getDefault()) !== null) {
                $default_query = "DEFAULT {$default}";
            } else {
                if ($field->isNull()) {
                    $default_query = 'DEFAULT NULL';
                }
            }

            $add_field_query = "{$field_name}
                {$field->getType()}({$field->getLength()})
                {$is_unsigned_query} {$is_null_query} {$default_query}";

            $fields_query[] = $add_field_query;
        }

        $fields_query = implode(', ', $fields_query);

        $query = "CREATE TABLE IF NOT EXISTS {$name} ({$fields_query} {$primary_query})  ENGINE={$engine}";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidQueryException();
        }

        return $res ? true : false;
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws DbAdapterException
     * @throws InvalidQueryException
     */
    static public function dropTable(string $name): bool {
        $query = "DROP TABLE IF EXISTS {$name}";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidQueryException();
        }

        return $res ? true : false;
    }

    /**
     * @param string $name
     * @param string $field_name
     * @param        $field
     *
     * @return bool
     * @throws DbAdapterException
     * @throws InvalidQueryException
     */
    static public function addColumn(string $name, string $field_name, $field): bool {
        $is_unsigned_query = '';
        $is_null_query = '';
        $default_query = '';

        if ($field->isUnsigned()) {
            $is_null_query = 'UNSIGNED';
        }

        if ($field->isNull()) {
            $is_null_query = 'NULL';
        } else {
            $is_null_query = 'NOT NULL';
        }

        if (($default = $field->getDefault()) !== null) {
            $default_query = "DEFAULT {$default}";
        } else {
            if ($field->isNull()) {
                $default_query = 'DEFAULT NULL';
            }
        }

        $add_field_query = "{$field_name}
            {$field->getType()}({$field->getLength()})
            {$is_unsigned_query} {$is_null_query} {$default_query}";

        $query = "ALTER TABLE `{$name}` ADD {$add_field_query}";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidQueryException();
        }

        return $res ? true : false;
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws DbAdapterException
     */
    static public function hasTable(string $name): bool {
        $query = "SHOW TABLES LIKE '{$name}'";
        $res = self::query($query);

        if (!$res) {
            return false;
        }

        if ($res->num_rows) {
            $res->free_result();
            return true;
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    static public function escape($value) {
        return self::conn()->escape_string($value);
    }

    /**
     * @param string $table_name
     * @param array $columns
     * @param Where  $where
     * @param bool   $for_update
     *
     * @return array
     * @throws DbAdapterException
     * @throws InvalidSelectQueryException
     * @throws InvalidWhereOperatorException
     */
    static public function get(string $table_name, array $columns = null, Where $where, bool $for_update = false): array {
        $for_update_query = '';
        if ($for_update) {
            $for_update_query = ' FOR UPDATE';
        }

        // Prepare columns, if argument is null select all columns
        if ($columns === null) {
            $columns_query = '*';
        } else {
            $columns_query = implode(',', array_map('trim', $columns));
        }

        $query = "SELECT {$columns_query} FROM `{$table_name}` WHERE {$where->build()} LIMIT 1 {$for_update_query}";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidSelectQueryException();
        }

        if ($row = $res->fetch_assoc()) {
            $res->free_result();
        }

        return $row ?: [];
    }

    /**
     * @param string     $table_name
     * @param array|null $columns
     * @param Where|null $where
     * @param array|null $group_by
     * @param array|null $order_by
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array
     * @throws DbAdapterException
     * @throws InvalidSelectQueryException
     * @throws InvalidWhereOperatorException
     */
    static public function select(string $table_name,
        array $columns = null, Where $where = null,
        array $group_by = null, array $order_by = null,
        int $limit = null, int $offset = null): array {

        // Prepare columns, if argument is null select all columns
        if ($columns === null) {
            $columns_query = '*';
        } else {
            $columns_query = implode(',', array_map('trim', $columns));
        }

        $query = "SELECT {$columns_query} FROM `{$table_name}`";

        // Prepare Where statement
        if ($where !== null) {
            $query .= " WHERE {$where->build()} ";
        }

        // Group result by table columns
        if ($group_by !== null) {
            if (count($group_by)) {
                $group_query = implode(',', array_map('trim', $group_by));
                $query .= " GROUP BY {$group_query} ";
            }
        }

        // Order result
        // array keys indicate columns by which result should be sorted
        // corresponding array key values indicate sorting direction
        if ($order_by !== null) {
            $order_query = [];
            foreach ($order_by as $order_item => $order_type) {
                if (!$order_type || !$order_item) {
                    throw new InvalidSelectQueryException();
                }

                $order_type = trim(strtoupper($order_type));
                if (!in_array($order_type, ['DESC', 'ASC'])) {
                    throw new InvalidSelectQueryException();
                }

                $order_query[] = "{$order_item} {$order_type}";
            }
            $order_query = implode(',', $order_query);

            $query .= " ORDER BY {$order_query} ";
        }

        if ($limit !== null) {
            $query .= " LIMIT {$limit} ";
        }

        if ($offset !== null) {
            $query .= " OFFSET {$offset} ";
        }

        // Execute query
        $res = self::query($query);

        if (!$res) {
            throw new InvalidSelectQueryException();
        }

        // Collect result
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $res->free_result();

        return $rows;
    }

    /**
     * @param string $table_name
     * @param array $fields
     * @param array $values
     *
     * @return int
     * @throws InvalidInsertQueryException
     * @throws DbAdapterException
     */
    static public function insert(string $table_name, array $fields, array $values): int {
        if (count($fields) != count($values)) {
            throw new InvalidInsertQueryException();
        }

        $query_fields = implode(',', array_map(function ($i) {
            return "`{$i}`";
        }, $fields));
        $query_values = implode(',', array_map(function ($i) {
            if ($i === null) {
                return 'NULL';
            } elseif (is_int($i) || is_float($i) || is_double($i)) {
                return $i;
            } else {
                return "'" . self::escape($i) . "'";
            }
        }, $values));

        $query = "INSERT INTO `{$table_name}` ({$query_fields}) VALUES ({$query_values})";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidInsertQueryException();
        }

        return $res ? self::conn()->insert_id : 0;
    }

    /**
     * @param string $table_name
     * @param array $data
     * @param Where $where
     *
     * @return bool
     * @throws DbAdapterException
     * @throws InvalidUpdateQueryException
     * @throws InvalidWhereOperatorException
     */
    static public function update(string $table_name, array $data, Where $where): bool {
        if (empty($data)) {
            throw new InvalidUpdateQueryException();
        }

        $query_data = [];
        foreach ($data as $field => $value) {
            if ($value === null) {
                $query_data[] = "`{$field}` = NULL";
            } elseif (is_int($value) || is_float($value) || is_double($value)) {
                $query_data[] = "`{$field}` = {$value}";
            } else {
                $query_data[] = "`{$field}` = '" . self::escape($value) . "'";
            }
        }
        $query_data = implode(',', $query_data);

        $query = "UPDATE `{$table_name}` SET {$query_data} WHERE {$where->build()}";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidUpdateQueryException();
        }

        return $res ? true : false;
    }

    /**
     * @param string $table_name
     * @param string $field
     * @param        $amount
     * @param Where  $where
     *
     * @return bool
     * @throws DbAdapterException
     * @throws InvalidUpdateQueryException
     * @throws InvalidWhereOperatorException
     */
    static public function add(string $table_name, string $field, $amount, Where $where): bool {
        $query = "UPDATE `{$table_name}` SET {$field} = ({$field} + ({$amount})) WHERE {$where->build()}";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidUpdateQueryException();
        }

        return $res ? true : false;
    }

    /**
     * @param string $table_name
     * @param Where $where
     *
     * @return bool
     * @throws DbAdapterException
     * @throws InvalidDeleteQueryException
     * @throws InvalidWhereOperatorException
     */
    static public function delete(string $table_name, Where $where): bool {
        $query = "DELETE FROM `{$table_name}` WHERE {$where->build()}";
        $res = self::query($query);

        if (!$res) {
            throw new InvalidDeleteQueryException();
        }

        return $res ? true : false;
    }
}
