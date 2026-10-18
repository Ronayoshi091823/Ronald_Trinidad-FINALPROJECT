<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function database(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $connection = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    migrateDatabase($connection);
    return $connection;
}

function migrateDatabase(PDO $connection): void
{
    $connection->exec(
        "CREATE TABLE IF NOT EXISTS reservations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            reservation_date DATE NOT NULL,
            reservation_time VARCHAR(30) NOT NULL,
            guests TINYINT UNSIGNED NOT NULL,
            status ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_reservations_date (reservation_date),
            INDEX idx_reservations_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $columns = $connection->query('SHOW COLUMNS FROM reservations')->fetchAll();
    $columnNames = array_column($columns, 'Field');
    if (!in_array('reservation_date', $columnNames, true) && in_array('res_date', $columnNames, true)) {
        $connection->exec('ALTER TABLE reservations CHANGE COLUMN res_date reservation_date DATE NULL DEFAULT NULL');
    }
    if (!in_array('reservation_time', $columnNames, true) && in_array('res_time', $columnNames, true)) {
        $connection->exec('ALTER TABLE reservations CHANGE COLUMN res_time reservation_time VARCHAR(30) NULL DEFAULT NULL');
    }
    if (!in_array('status', $columnNames, true)) {
        $connection->exec(
            "ALTER TABLE reservations ADD COLUMN status ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed'"
        );
    }
    if (!in_array('updated_at', $columnNames, true)) {
        $connection->exec(
            'ALTER TABLE reservations ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
             ON UPDATE CURRENT_TIMESTAMP'
        );
    }
    $columns = $connection->query('SHOW COLUMNS FROM reservations')->fetchAll();
    $columnTypes = [];
    foreach ($columns as $column) {
        $columnTypes[$column['Field']] = $column['Type'];
    }
    foreach (['name' => '120', 'email' => '190', 'phone' => '40'] as $column => $length) {
        if (($columnTypes[$column] ?? '') !== 'varchar(' . $length . ')') {
            $connection->exec('ALTER TABLE reservations MODIFY ' . $column . ' VARCHAR(' . $length . ') NULL');
        }
    }

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS admins (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(60) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM(\'admin\', \'staff\') NOT NULL DEFAULT \'admin\',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $adminColumns = array_column($connection->query('SHOW COLUMNS FROM admins')->fetchAll(), 'Field');
    if (!in_array('role', $adminColumns, true)) {
        $connection->exec("ALTER TABLE admins ADD COLUMN role ENUM('admin', 'staff') NOT NULL DEFAULT 'admin'");
    }
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS reservation_history (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            reservation_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(20) NOT NULL,
            snapshot LONGTEXT NOT NULL,
            actor VARCHAR(60) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_history_reservation (reservation_id),
            INDEX idx_history_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $legacyRows = $connection->query(
        'SELECT r.id, r.name, r.email, r.phone, r.reservation_date AS date,
                r.reservation_time AS time, r.guests, r.status, r.created_at, r.updated_at
         FROM reservations r
         LEFT JOIN reservation_history h ON h.reservation_id = r.id
         WHERE h.id IS NULL'
    )->fetchAll();
    if ($legacyRows) {
        $insert = $connection->prepare(
            'INSERT INTO reservation_history (reservation_id, action, snapshot, actor, created_at)
             VALUES (:reservation_id, :action, :snapshot, :actor, :created_at)'
        );
        foreach ($legacyRows as $reservation) {
            $insert->execute([
                'reservation_id' => $reservation['id'],
                'action' => 'imported',
                'snapshot' => json_encode(
                    $reservation,
                    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
                ),
                'actor' => 'Legacy import',
                'created_at' => $reservation['created_at'],
            ]);
        }
    }
}
