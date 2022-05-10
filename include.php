<?php

$root = __DIR__;

/*
 * Init Kernel
 *
 * Create kernel instance:
 * - set configurations,
 * - set autoloader,
 * - set db connection,
 * - set router
 */
require_once $root . '/Core/Engine/Kernel.php';
$kernel = new Engine\Kernel($root,  'config.php');
