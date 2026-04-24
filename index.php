<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__) . '/escola');

require_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/app/Helpers/Helpers.php';
require_once __DIR__ . '/config/constants.php';

date_default_timezone_set(TIMEZONE);


// iniciar aplicação
require __DIR__ . '/bootstrap/app.php';
