<?php
if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'development') {
    http_response_code(403);
    exit('Development CLI only.');
}
require_once __DIR__ . '/../conn.inc';
$password = getenv('DEV_PASSWORD');
if (!$password || strlen($password) < 8 || strlen($password) > 72) {
    throw new RuntimeException('Set DEV_PASSWORD to 8–72 bytes.');
}
db()->beginTransaction();
try {
    foreach ([['dill', 1, 1000, 3], ['bob', 1, 10, 3], ['admin', 2, 10, 10]] as [$name, $permissions, $gold, $hp]) {
        if (db_query('SELECT username FROM users WHERE username = ?', [$name])->fetch()) continue;
        db_query('INSERT INTO users (username,password,email,permissions,class,gold,hp,experience,location,turns) VALUES (?,?,?,?,1,?,?,100,1,10)',
            [$name, password_hash($password, PASSWORD_DEFAULT), "$name@example.test", $permissions, $gold, $hp]);
        foreach (match ($name) { 'dill' => [1, 2, 3], 'bob' => [1, 2], default => [] } as $item) {
            db_query('INSERT INTO inventory (owner,itemtype,qty) VALUES (?,?,1)', [$name, $item]);
        }
    }
    if (!db_query('SELECT id FROM administrators WHERE username = ?', ['admin'])->fetch()) {
        db_query('INSERT INTO administrators (username,password,first_name,last_name,email,admin_level) VALUES (?,?,?,?,?,?)',
            ['admin', password_hash($password, PASSWORD_DEFAULT), 'Development', 'Administrator', 'admin@example.test', 2]);
    }
    db()->commit();
    echo "Development accounts ready: dill, bob, admin. Existing accounts were left unchanged.\n";
} catch (Throwable $error) {
    db()->rollBack();
    throw $error;
}
