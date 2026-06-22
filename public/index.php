<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Sanitize server variables to remove CR/LF/TAB characters
// This fixes a Railway proxy issue where CRLF characters appear in REQUEST_URI
foreach (['REQUEST_URI', 'HTTP_HOST', 'QUERY_STRING', 'PATH_INFO'] as $key) {
    if (isset($_SERVER[$key])) {
        $_SERVER[$key] = str_replace(["\r", "\n", "\t"], '', $_SERVER[$key]);
    }
}

$app->handleRequest(Request::capture());
