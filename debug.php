<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($e) {
    echo "<h2>🔥 Exception Caught</h2>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Trace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        echo "<h2>💥 Fatal Error</h2>";
        echo "<pre>";
        print_r($error);
        echo "</pre>";
    }
});
