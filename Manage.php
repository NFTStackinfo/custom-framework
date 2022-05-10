<?php

use Engine\Debugger\Traceback;

use Core\Command\DefaultManager;
use Core\Command\Exception\InvalidParamException;

// Include index
require_once 'include.php';

// Configure argument parser
$short_commands = 'c:';
$long_commands = [
    'name:',
    'prefix:',
];
$arguments = getopt($short_commands, $long_commands);

// Pass command to DefaultManager and execute
try {
    DefaultManager::command($arguments);
    exit();
} catch (InvalidParamException $e) {
    $response = "Param `{$e->getMessage()}` is` required";
} catch (Exception $e) {
    $response = Traceback::stringifyException($e);
}

echo $response . "\n";
exit();
