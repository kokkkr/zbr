<?php
declare(strict_types=1);

/**
 * Router script for PHP built-in server on Railway
 * Run: php -S 0.0.0.0:$PORT server.php
 */

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = __DIR__ . $uri;

// 1) Serve existing static files directly
if ($uri !== '/' && is_file($path)) {
    return false; // let built-in server serve it
}

// 2) Rewrite /app/... to router.php?path=...
if (preg_match('~^/app(?:/(.*))?$~', $uri, $m)) {
    $_GET['path'] = isset($m[1]) ? (string)$m[1] : '';
    require __DIR__ . '/router.php';
    exit;
}

// 3) Default route -> index.php
require __DIR__ . '/index.php';
