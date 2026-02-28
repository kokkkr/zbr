<?php
declare(strict_types=1);

namespace App;

final class Db
{
    public static function pdo(): \PDO
    {
        // قراءة القيم من ENV
        $dsn  = $_ENV['DB_DSN']  ?? 'mysql:host=127.0.0.1;dbname=app;charset=utf8mb4';
        $user = $_ENV['DB_USER'] ?? 'app_user';
        $pass = $_ENV['DB_PASS'] ?? 'change_me';

        // تحقق من وجود PDO و pdo_mysql
        if (!class_exists(\PDO::class)) {
            die("🔥 PDO extension is not enabled.");
        }

        if (str_starts_with($dsn, 'mysql:') && !in_array('mysql', \PDO::getAvailableDrivers(), true)) {
            die("🔥 pdo_mysql driver is not installed on the server.");
        }

        try {
            $pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            if (str_starts_with($dsn, 'mysql:')) {
                $pdo->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
                $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            return $pdo;

        } catch (\PDOException $e) {
            echo "<h2>💥 Database Connection Error</h2>";
            echo "<pre>";
            echo "Message: " . $e->getMessage() . "\n\n";
            echo "DSN: " . $dsn . "\n";
            echo "User: " . $user . "\n";
            echo "</pre>";
            exit;
        }
    }
}
