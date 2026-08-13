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
    ensure_schema($pdo);
    return $pdo;
}
function ensure_schema(PDO $pdo): void {
    static $done = false;
    if ($done) return;
    $pdo->exec("CREATE TABLE IF NOT EXISTS vehicles(id INT AUTO_INCREMENT PRIMARY KEY,make VARCHAR(80),model VARCHAR(80),category VARCHAR(40),transmission VARCHAR(20),daily_rate DECIMAL(10,2),active TINYINT DEFAULT 1)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings(id INT AUTO_INCREMENT PRIMARY KEY,customer_name VARCHAR(120),phone VARCHAR(40),vehicle_id INT,pickup_date DATE,return_date DATE,pickup_location VARCHAR(120),total DECIMAL(10,2),status VARCHAR(30),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(vehicle_id) REFERENCES vehicles(id))");
    if ((int)$pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn() === 0) {
        $pdo->exec("INSERT INTO vehicles(make,model,category,transmission,daily_rate) VALUES ('Dacia','Sandero','economy','manual',300),('Renault','Clio','automatic','automatic',380),('Hyundai','Tucson','suv','automatic',650),('Mercedes','C-Class','premium','automatic',1100)");
    }
    $done = true;
}
function json_out($data, int $status=200): never { http_response_code($status); header('Content-Type: application/json'); echo json_encode($data); exit; }
