<?php
// Define que os erros devem ser registados num ficheiro
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Define o caminho para o ficheiro (ex: pasta 'logs' no seu projeto)
// __DIR__ garante que o caminho é relativo ao ficheiro atual
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

define('BASE_PATH', dirname(__DIR__) . '/escola');

require_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/app/Helpers/Helpers.php';
require_once __DIR__ . '/config/constants.php';

date_default_timezone_set(TIMEZONE);


// iniciar aplicação
require __DIR__ . '/bootstrap/app.php';
