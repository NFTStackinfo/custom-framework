<?php

namespace Engine\Debugger;

use Exception;

class Traceback {
    /**
     * @param Exception $e
     *
     * @return mixed
     */
    public static function stringifyException(Exception $e) {
        $traceback = [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        foreach ($e->getTrace() as $trace) {
            $traceback[] = [
                'file' => isset($trace['file']) ? $trace['file'] : null,
                'line' => isset($trace['line']) ? $trace['line'] : null,
                'class' => isset($trace['class']) ? $trace['class'] : null,
                'function' => isset($trace['function']) ? $trace['function'] : null,
                'args' => isset($trace['args']) ? $trace['args'] : null,
            ];
        }

        return print_r($traceback, true);
    }

    /**
     * @param mixed ...$debug_data
     *
     * @return string
     */
    public static function pretty(...$debug_data): string {
        $pretty = [];
        foreach ($debug_data as $data) {
            $pretty[] = '<pre>' . print_r($data, true) . '</pre>';
        }

        return implode('<br />', $pretty);
    }
}