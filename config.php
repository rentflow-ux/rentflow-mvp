<?php
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
    if ($url) {
        $p = parse_url($url);
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $p['host'], $p['port'] ?? 3306, ltrim($p['path'], '/'));
        $pdo = new PDO($dsn, urldecode($p['user']), urldecode($p['pass']), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } else {
        $pdo = new PDO('mysql:host='.getenv('MYSQLHOST').';port='.(getenv('MYSQLPORT') ?: 3306).';dbname='.getenv('MYSQLDATABASE').';charset=utf8mb4', getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    return $pdo;
}
function json_out($data, int $status=200): never { http_response_code($status); header('Content-Type: application/json'); echo json_encode($data); exit; }

