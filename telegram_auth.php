<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/app/Bootstrap.php';
require_once __DIR__ . '/app/Security.php';
require_once __DIR__ . '/app/Db.php';
require_once __DIR__ . '/app/Telegram.php';

use App\Security;
use App\Db;
use App\Telegram;

function fail(string $msg): void {
    http_response_code(500);
    echo "<h2 style='color:red'>ERROR:</h2><pre>{$msg}</pre>";
    exit;
}

function tgdbg(string $m): void {
    @file_put_contents(__DIR__ . '/tg-auth-debug.log',
        '[' . date('c') . "] " . $m . "\n",
        FILE_APPEND
    );
}

try {

    $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
    $requireAllow = filter_var($_ENV['TELEGRAM_REQUIRE_ALLOWLIST'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
    $allowIds = Telegram::parseAllowlist($_ENV['TELEGRAM_ALLOWED_IDS'] ?? '');
    $announceChat = $_ENV['TELEGRAM_ANNOUNCE_CHAT_ID'] ?? '';

    if ($botToken === '') {
        fail("Missing TELEGRAM_BOT_TOKEN in .env");
    }

    if (!isset($_GET['hash'])) {
        fail("Missing Telegram hash parameter");
    }

    if (!Telegram::verify($_GET, $botToken, 900)) {
        fail("Telegram verification failed");
    }

    $tgId  = (string)($_GET['id'] ?? '');
    $tUser = (string)($_GET['username'] ?? '');
    $first = (string)($_GET['first_name'] ?? '');
    $last  = (string)($_GET['last_name'] ?? '');
    $photo = (string)($_GET['photo_url'] ?? '');

    if ($requireAllow && !in_array($tgId, $allowIds, true)) {
        fail("User not in allowlist");
    }

    $pdo = Db::pdo();

    if (!$pdo) {
        fail("PDO connection failed");
    }

    $res = Telegram::saveUser($pdo, $tgId, $tUser, $first, $last, $photo, 100);

    if (!($res['ok'] ?? false)) {
        fail("saveUser failed");
    }

    $uid = (int)($res['id'] ?? 0);
    $status = strtolower((string)($res['status'] ?? 'free'));
    $created = (bool)($res['created'] ?? false);

    $_SESSION['uid'] = $uid;
    $_SESSION['uname'] = $tUser ?: ('tg_' . $tgId);
    $_SESSION['last_login'] = time();
    session_regenerate_id(true);

    // Announce (optional)
    if ($announceChat !== '') {
        $display = trim(($first.' '.$last)) ?: ($tUser ?: $tgId);
        $displaySafe = htmlspecialchars($display, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $text = $created
            ? "🎉 <b>New member</b>: <b>{$displaySafe}</b>\n➡️ <a href=\"https://zbr-production-d7e1.up.railway.app\">Open</a>"
            : "🌟 <b>{$displaySafe}</b> logged in.\n➡️ <a href=\"https://zbr-production-d7e1.up.railway.app/app/dashboard\">Dashboard</a>";

        Telegram::sendMessage($botToken, $announceChat, $text, 'HTML');
    }

    $next = '/views/dashboard';

    if (!empty($_GET['state']) &&
        is_string($_GET['state']) &&
        preg_match('~^/app(?:/[\w\-]+)?$~', $_GET['state'])
    ) {
        $next = $_GET['state'];
    }

    header("Location: https://zbr-production-d7e1.up.railway.app{$next}");
    exit;

} catch (Throwable $e) {
    fail($e->getMessage() . "\n\n" . $e->getTraceAsString());
}
