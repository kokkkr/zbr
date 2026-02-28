<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* ==============================
   CONFIG
============================== */

$BOT_TOKEN = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
$BASE_URL  = "https://zbr-production-d7e1.up.railway.app";

/* ==============================
   VERIFY TELEGRAM LOGIN
============================== */

if (!$BOT_TOKEN) {
    die("Bot token missing in ENV");
}

if (!isset($_GET['hash'])) {
    http_response_code(400);
    exit("Invalid request");
}

function verifyTelegram(array $data, string $botToken): bool {
    $checkHash = $data['hash'];
    unset($data['hash']);

    ksort($data);

    $dataCheckString = '';
    foreach ($data as $key => $value) {
        $dataCheckString .= $key . '=' . $value . "\n";
    }
    $dataCheckString = rtrim($dataCheckString, "\n");

    $secretKey = hash('sha256', $botToken, true);
    $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

    return hash_equals($hash, $checkHash);
}

if (!verifyTelegram($_GET, $BOT_TOKEN)) {
    http_response_code(403);
    exit("Verification failed");
}

/* ==============================
   GET USER DATA
============================== */

$tgId  = $_GET['id'] ?? '';
$user  = $_GET['username'] ?? '';
$first = $_GET['first_name'] ?? '';
$last  = $_GET['last_name'] ?? '';
$photo = $_GET['photo_url'] ?? '';

if (!$tgId) {
    exit("Missing Telegram ID");
}

/* ==============================
   DATABASE CONNECTION
============================== */

$dsn  = $_ENV['DB_DSN'] ?? '';
$dbUser = $_ENV['DB_USER'] ?? '';
$dbPass = $_ENV['DB_PASS'] ?? '';

if (!$dsn) {
    exit("DB config missing");
}

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Throwable $e) {
    die("DB ERROR: " . $e->getMessage());
}

/* ==============================
   CREATE TABLE IF NOT EXISTS
============================== */

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telegram_id VARCHAR(50) UNIQUE,
    username VARCHAR(100),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    profile_picture TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* ==============================
   UPSERT USER
============================== */

$stmt = $pdo->prepare("
INSERT INTO users (telegram_id, username, first_name, last_name, profile_picture)
VALUES (:tg, :u, :f, :l, :p)
ON DUPLICATE KEY UPDATE
username = VALUES(username),
first_name = VALUES(first_name),
last_name = VALUES(last_name),
profile_picture = VALUES(profile_picture)
");

$stmt->execute([
    ':tg' => $tgId,
    ':u'  => $user,
    ':f'  => $first,
    ':l'  => $last,
    ':p'  => $photo
]);

/* ==============================
   SESSION
============================== */

$_SESSION['uid'] = $tgId;
$_SESSION['username'] = $user;

/* ==============================
   REDIRECT
============================== */

header("Location: $BASE_URL/app/dashboard");
exit;
