<?php

namespace marcocesarato\amwscan;

include_once 'Actions.php';
include_once 'Argument.php';
include_once 'Argv.php';
include_once 'Console.php';
include_once 'Definitions.php';
include_once 'Flag.php';
include_once 'Deobfuscator.php';
include_once 'Application.php';

if (!Application::isCli()) {
    trigger_error('This file should run from a console session.', E_USER_WARNING);
}

// Settings
ini_set('memory_limit', '1G');
ini_set('xdebug.max_nesting_level', 500);
ob_implicit_flush(false);
set_time_limit(-1);

// Errors
error_reporting(0);
ini_set('display_errors', 0);

$app = new Application();
$app->run();
