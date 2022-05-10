<?php

namespace Engine\Parser;

use Engine\Parser\Exception\InvalidParamException;

class Parser {
    private $input = null;

    private $url_parameters = [];

    /**
     * Parser constructor.
     *
     * @param array $url_parameters
     */
    function __construct(array $url_parameters = []) {
        $this->url_parameters = $url_parameters;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    private function urlParameter(string $name) {
       return isset($this->url_parameters[$name]) ? $this->url_parameters[$name] : null;
    }

    /**
     * @param string $field
     *
     * @return mixed|null
     */
    private function getFromInput(string $field) {
        if ($this->input === null) {
            $this->input = [];
            parse_str(utf8_decode(urldecode((file_get_contents("php://input")))), $this->input);
        }

        return isset($this->input[$field]) ? $this->input[$field] : null;
    }

    /**
     * @param string $field
     *
     * @return mixed|string|null
     */
    private function getField(string $field) {
        $value = null;

        if (isset($_REQUEST[$field])) {
            $value = $_REQUEST[$field];
        }

        if ($value === null) {
            $value = $this->getFromInput($field);
        }

        if ($value === null) {
            $value = $this->urlParameter($field);
        }

        if ($value === null) {
            return null;
        }

        $value = addslashes($value);

        return $value;
    }

    /**
     * @param string $field
     * @param array $filters
     *
     * @return float|int|mixed|string|null
     * @throws InvalidParamException
     */
    public function get(string $field, array $filters = []) {
        $normalized_filters = [];
        foreach ($filters as $k => $v) {
            if (is_numeric($k)) {
                $normalized_filters[$v] = 1;
            } else {
                $normalized_filters[$k] = $v;
            }
        }

        $priority_filters = array_intersect(
            ['required', 'default', 'int', 'double', 'positive'],
            array_keys($normalized_filters));
        $filters = array_merge(array_flip($priority_filters), $normalized_filters);

        $value = $this->getField($field);
        if ($value !== null && !isset($filters['skip_trim'])) {
            $value = trim($value);
        }

        foreach ($filters as $filter => $settings) {
            switch ($filter) {
                case 'required':
                    if ($value === null || $value === '') {
                        throw new InvalidParamException("{$field} param is required");
                    }
                    break;
                case 'default':
                    if ($value === null) {
                        $value = $settings;
                    }
                    break;
                case 'maxLen':
                    if ($value !== null && mb_strlen($value) > $settings) {
                        throw new InvalidParamException("maximum length for param {$field} is {$settings}");
                    }
                    break;
                case 'minLen':
                    if ($value !== null && mb_strlen($value) < $settings) {
                        throw new InvalidParamException("minimum length for param {$field} is {$settings}");
                    }
                    break;
                case 'int':
                    if ($value !== null) {
                        if (!is_numeric($value)) {
                            throw new InvalidParamException("{$field} should be integer");
                        }
                        $value = (int)$value;
                    }
                    break;
                case 'double':
                    if ($value !== null) {
                        if (!is_numeric($value)) {
                            throw new InvalidParamException("{$field} should be double");
                        }
                        $value = (double)$value;
                    }
                    break;
                case 'positive':
                    if ($value !== null && (!is_numeric($value) || $value < 0)) {
                        throw new InvalidParamException("{$field} should be positive number");
                    }
                    break;
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidParamException("{$field} not valid email address");
                    }
                    break;
                case 'lowercase':
                    if ($value !== null) {
                        $value = mb_strtolower($value);
                    }
                    break;
                case 'uppercase':
                    if ($value !== null) {
                        $value = mb_strtoupper($value);
                    }
                    break;
                case 'oneOf':
                    if (!in_array($value, $settings, true)) {
                        throw new InvalidParamException("{$field} should be one of " . implode(', ', $settings));
                    }
                    break;
            }
        }

        return $value;
    }
}