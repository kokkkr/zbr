<?php
declare(strict_types=1);

@date_default_timezone_set('UTC');

/* ===============================
   SIMPLE ENV LOADER
================================= */
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;

        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim(trim($v), "\"'");
    }
}

/* ===============================
   HTTPS DETECTION
================================= */
$isHttps =
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

/* ===============================
   SESSION CONFIG (SAFE FOR RAILWAY)
================================= */
$SESSION_NAME    = $_ENV['SESSION_NAME'] ?? 'CYBORXSESSID';
$COOKIE_LIFETIME = (int)($_ENV['SESSION_COOKIE_LIFETIME'] ?? 7200);
$GC_MAXLIFETIME  = (int)($_ENV['SESSION_GC_MAXLIFETIME'] ?? 7200);
$SAMESITE        = $_ENV['SESSION_SAMESITE'] ?? 'Lax';

/* مهم جداً */
$COOKIE_DOMAIN = '';   // 🔥 لا تستخدم .railway.app

ini_set('session.gc_maxlifetime', (string)$GC_MAXLIFETIME);
ini_set('session.cookie_lifetime', (string)$COOKIE_LIFETIME);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_name($SESSION_NAME);

session_set_cookie_params([
    'lifetime' => $COOKIE_LIFETIME,
    'path'     => '/',
    'domain'   => $COOKIE_DOMAIN,   // فارغ
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => $SAMESITE,
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
