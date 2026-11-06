<?php
function roomBasePath(): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_contains($script, '/components/') ? '../../' : '';
}
function requireLogin(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (($_SESSION['room_logged_in'] ?? false) !== true) {
        header('Location: ' . roomBasePath() . 'components/LOGIN/room_login.php');
        exit;
    }
}

function requireAdminTeacher(PDO $pdo): void {
    requireLogin();
    $stmt = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
    $stmt->execute([positiveId($_SESSION['room_user_id'] ?? null)]);
    if ($stmt->fetchColumn() !== 'admin') {
        http_response_code(403);
        exit('Only admin/teacher accounts can manage rooms.');
    }
}

function roomRoleLabel(string $role): string {
    return match ($role) {
        'admin' => 'Admin/Teacher',
        'student', 'user' => 'Student',
        default => 'Account',
    };
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function positiveId(mixed $value): int {
    return filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]) ?: 0;
}
function page(string $title, string $body): void {
    $base = roomBasePath();
    $currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<link rel="stylesheet" href="'.$base.'Style/styles.css?v=43"><title>'.e($title).'</title></head>';
    echo '<body class="dashboard-page">';
    require __DIR__ . '/components/Dashboard/room_sidebar.php';
    echo '<main class="dashboard-content">';
    echo '<h1'.($currentPage === 'room_index.php' ? ' class="dashboard-heading"' : '').'>'.e($title).'</h1>'.$body.'</main></body></html>';
}
