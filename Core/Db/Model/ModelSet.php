<?php

namespace Db\Model;

use Iterator;

use Db\Model\Exception\ModelNotFoundException;

class ModelSet implements Iterator {
    private $position = 0;

    private $items = [];

    /**
     * ModelSet constructor.
     *
     * @param array $items
     */
    function __construct(array $items = []) {
        $this->position = 0;
        $this->items = $items;
    }

    /**
     * Iterator interface method
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * Iterator interface method
     */
    public function current() {
        $keys = array_keys($this->items);
        $key = $keys[$this->position];
        return $this->items[$key];
    }

    /**
     * Iterator interface method
     */
    public function key() {
        $keys = array_keys($this->items);
        return $keys[$this->position];
    }

    /**
     * Iterator interface method
     */
    public function next() {
        ++$this->position;
    }

    /**
     * Iterator interface method
     */
    public function valid() {
        return $this->position < count($this->items);
    }

    /**
     * @return Model
     * @throws ModelNotFoundException
     */
    public function first() {
        if (empty($this->items)) {
            throw new ModelNotFoundException();
        }

        return $this->items[0];
    }

    /**
     * @param string $column_name
     *
     * @return array
     */
    public function column(string $column_name): array {
        $result = [];
        foreach ($this->items as $item) {
            $result[] = $item->$column_name;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return empty($this->items);
    }

    /**
     * @param string $serializer
     *
     * @return array
     */
    public function serialize(string $serializer): array {
        $serialized_result = [];
        foreach ($this as $item) {
            $serialized_result[] = $serializer($item);
        }

        return $serialized_result;
    }

    /**
     * @param callable $callback
     *
     * @return array
     */
    public function map(callable $callback): array {
        $array_result = [];
        foreach ($this as $item) {
            $array_result[] = $callback($item);
        }

        return $array_result;
    }
}