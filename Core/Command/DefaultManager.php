<?php

namespace Core\Command;

use Core\Command\Exception\InvalidParamException;
use Core\Command\Exception\NoCommandSpecifiedException;
use Core\Command\Exception\UnknownCommandException;

class DefaultManager {
    /**
     * @param array $arguments
     *
     * @throws InvalidParamException
     * @throws NoCommandSpecifiedException
     * @throws UnknownCommandException
     */
    public static function command(array $arguments) {
        $inst = null;

        if (!isset($arguments['c'])) {
            throw new NoCommandSpecifiedException();
        }
        $command = $arguments['c'];

        switch ($command) {
            case 'migrate':
                $prefix = self::getCommandParam('prefix', $arguments, false);
                $inst = new Migrate($prefix);
                break;
            case 'migration':
                $name = self::getCommandParam('name', $arguments);
                $prefix = self::getCommandParam('prefix', $arguments, false);
                $inst = new Migration($name, $prefix);
                break;
            default:
                throw new UnknownCommandException();
        }

        $inst->exec();
    }

    /**
     * @param string $arg
     * @param array $arguments
     * @param bool $required
     *
     * @return mixed|null
     * @throws InvalidParamException
     */
    private static function getCommandParam(string $arg, array $arguments, bool $required = true) {
        if (isset($arguments[$arg])) {
            return trim($arguments[$arg]);
        } else {
            if ($required) {
                throw new InvalidParamException($arg);
            }
        }

        return null;
    }
}