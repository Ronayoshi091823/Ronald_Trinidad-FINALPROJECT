<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Strict',
]);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function requestData(): array
{
    $body = file_get_contents('php://input');
    $data = json_decode($body ?: '', true);
    if (!is_array($data)) {
        respond(['error' => 'Invalid request data.'], 400);
    }
    return $data;
}

function requireCsrfToken(array $data): void
{
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), (string)($data['csrf_token'] ?? ''))) {
        respond(['error' => 'Your session expired. Please refresh the page and try again.'], 403);
    }
}

function requireAdmin(array $data = []): void
{
    if (empty($_SESSION['admin_id'])) {
        respond(['error' => 'Please sign in as an administrator.'], 401);
    }
    requireCsrfToken($data);
}

function validateReservation(array $data): array
{
    $reservation = [
        'name' => trim((string)($data['name'] ?? '')),
        'email' => trim((string)($data['email'] ?? '')),
        'phone' => trim((string)($data['phone'] ?? '')),
        'date' => trim((string)($data['date'] ?? '')),
        'time' => trim((string)($data['time'] ?? '')),
        'guests' => trim((string)($data['guests'] ?? '')),
    ];
    if ($reservation['name'] === '' || mb_strlen($reservation['name']) > 120) {
        respond(['error' => 'Enter a name with no more than 120 characters.'], 422);
    }
    if (!filter_var($reservation['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($reservation['email']) > 190) {
        respond(['error' => 'Enter a valid email address.'], 422);
    }
    if ($reservation['phone'] === '' || mb_strlen($reservation['phone']) > 40) {
        respond(['error' => 'Enter a phone number with no more than 40 characters.'], 422);
    }
    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $reservation['date']);
    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $reservation['date']) {
        respond(['error' => 'Enter a valid reservation date.'], 422);
    }
    if ($reservation['time'] === '' || mb_strlen($reservation['time']) > 30) {
        respond(['error' => 'Enter a valid reservation time.'], 422);
    }
    if (!ctype_digit($reservation['guests']) || (int)$reservation['guests'] < 1 || (int)$reservation['guests'] > 30) {
        respond(['error' => 'Choose a valid number of guests.'], 422);
    }
    return $reservation;
}

function saveHistory(PDO $db, array $reservation, string $action): void
{
    $statement = $db->prepare(
        'INSERT INTO reservation_history (reservation_id, action, snapshot, actor)
         VALUES (:reservation_id, :action, :snapshot, :actor)'
    );
    $statement->execute([
        'reservation_id' => $reservation['id'],
        'action' => $action,
        'snapshot' => json_encode($reservation, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        'actor' => $_SESSION['admin_username'] ?? 'Guest',
    ]);
}

$action = (string)($_GET['action'] ?? '');
try {
    $db = database();

    if ($action === 'create') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respond(['error' => 'Method not allowed.'], 405);
        }
        $reservation = validateReservation(requestData());
        if ($reservation['date'] < date('Y-m-d')) {
            respond(['error' => 'Choose a reservation date that is today or later.'], 422);
        }
        $db->beginTransaction();
        $statement = $db->prepare(
            'INSERT INTO reservations (name, email, phone, reservation_date, reservation_time, guests)
             VALUES (:name, :email, :phone, :date, :time, :guests)'
        );
        $statement->execute($reservation);
        $reservation['id'] = (int)$db->lastInsertId();
        $reservation['status'] = 'confirmed';
        $reservation['created_at'] = date('Y-m-d H:i:s');
        $reservation['updated_at'] = $reservation['created_at'];
        saveHistory($db, $reservation, 'created');
        $db->commit();
        respond(['success' => true]);
    }

    if ($action === 'login') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respond(['error' => 'Method not allowed.'], 405);
        }
        $data = requestData();
        $username = trim((string)($data['username'] ?? ''));
        $statement = $db->prepare('SELECT id, username, password_hash, role FROM admins WHERE username = :username');
        $statement->execute(['username' => $username]);
        $admin = $statement->fetch();
        if (!$admin || !password_verify((string)($data['password'] ?? ''), $admin['password_hash'])) {
            respond(['error' => 'Invalid username or password.'], 401);
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        respond(['success' => true, 'csrf_token' => $_SESSION['csrf_token'], 'role' => $admin['role']]);
    }

    if ($action === 'status') {
        if (empty($_SESSION['admin_id'])) {
            $_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
            respond(['authenticated' => false, 'csrf_token' => $_SESSION['csrf_token']]);
        }
        $statement = $db->prepare('SELECT role FROM admins WHERE id = :id');
        $statement->execute(['id' => (int)$_SESSION['admin_id']]);
        $role = $statement->fetchColumn();
        if ($role !== 'admin' && $role !== 'staff') {
            $_SESSION = [];
            session_destroy();
            respond(['authenticated' => false]);
        }
        $_SESSION['admin_role'] = $role;
        respond([
            'authenticated' => true,
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
            'role' => $role,
        ]);
    }

    if ($action === 'create-staff') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respond(['error' => 'Method not allowed.'], 405);
        }
        $data = requestData();
        requireCsrfToken($data);
        $username = trim((string)($data['username'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $passwordConfirm = (string)($data['password_confirm'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_-]{3,60}$/', $username)) {
            respond(['error' => 'Use a username of 3–60 letters, numbers, underscores, or hyphens.'], 422);
        }
        if (strlen($password) < 12 || strlen($password) > 200) {
            respond(['error' => 'The password must be between 12 and 200 characters.'], 422);
        }
        if (!hash_equals($password, $passwordConfirm)) {
            respond(['error' => 'The passwords do not match.'], 422);
        }

        $statement = $db->prepare(
            'INSERT INTO admins (username, password_hash, role)
             VALUES (:username, :password_hash, \'staff\')'
        );
        $statement->execute([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        respond(['success' => true, 'message' => 'Staff account created.']);
    }

    if ($action === 'dashboard') {
        if (empty($_SESSION['admin_id'])) {
            respond(['error' => 'Please sign in as an administrator.'], 401);
        }
        $reservations = $db->query(
            'SELECT id, name, email, phone, reservation_date AS date, reservation_time AS time,
                    guests, status, created_at, updated_at
             FROM reservations ORDER BY reservation_date DESC, created_at DESC'
        )->fetchAll();
        $history = $db->query(
            'SELECT reservation_id, action, snapshot, actor, created_at
             FROM reservation_history ORDER BY created_at DESC, id DESC'
        )->fetchAll();
        respond([
            'reservations' => $reservations,
            'history' => $history,
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
        ]);
    }

    if ($action === 'logout') {
        $data = requestData();
        requireAdmin($data);
        $_SESSION = [];
        session_destroy();
        respond(['success' => true]);
    }

    if ($action === 'update' || $action === 'cancel') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respond(['error' => 'Method not allowed.'], 405);
        }
        $data = requestData();
        requireAdmin($data);
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            respond(['error' => 'Invalid reservation.'], 422);
        }
        $db->beginTransaction();
        $statement = $db->prepare('SELECT * FROM reservations WHERE id = :id FOR UPDATE');
        $statement->execute(['id' => $id]);
        $existing = $statement->fetch();
        if (!$existing) {
            $db->rollBack();
            respond(['error' => 'Reservation not found.'], 404);
        }

        if ($action === 'update') {
            $reservation = validateReservation($data);
            $statement = $db->prepare(
                'UPDATE reservations
                 SET name = :name, email = :email, phone = :phone,
                     reservation_date = :date, reservation_time = :time,
                     guests = :guests, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $statement->execute($reservation + ['id' => $id]);
            $historyAction = 'updated';
        } else {
            if ($existing['status'] !== 'cancelled') {
                $statement = $db->prepare(
                    "UPDATE reservations SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = :id"
                );
                $statement->execute(['id' => $id]);
            }
            $historyAction = 'cancelled';
        }

        $statement = $db->prepare(
            'SELECT id, name, email, phone, reservation_date AS date, reservation_time AS time,
                    guests, status, created_at, updated_at
             FROM reservations WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $current = $statement->fetch();
        saveHistory($db, $current, $historyAction);
        $db->commit();
        respond(['success' => true]);
    }

    respond(['error' => 'Unknown action.'], 404);
} catch (PDOException $error) {
    error_log('PUREMENT database error: ' . $error->getMessage());
    if ($action === 'create-staff' && $error->getCode() === '23000') {
        respond(['error' => 'That username is already in use.'], 409);
    }
    respond(['error' => 'Database unavailable. Import database.sql in phpMyAdmin and check config.php.'], 500);
} catch (JsonException $error) {
    error_log('PUREMENT JSON error: ' . $error->getMessage());
    respond(['error' => 'Could not record reservation history.'], 500);
}
