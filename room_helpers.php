<?php
function roomBasePath(): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (str_contains($script, '/components/')) return '../../';
    if (str_contains($script, '/AI/')) return '../';
    return '';
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
        exit('Only admin/teacher accounts can access this page.');
    }
}

function roomRoleLabel(string $role): string {
    return match ($role) {
        'admin' => 'Admin/Teacher',
        'student', 'user' => 'Student',
        default => 'Account',
    };
}

function roomBuildingOptions(): array {
    return ['MET Building', 'Engineering Building', 'Library'];
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
    $profileId = positiveId($_SESSION['room_user_id'] ?? null);
    $profileName = trim((string) ($_SESSION['room_username'] ?? '')) ?: 'Account';
    $profileRole = roomRoleLabel((string) ($_SESSION['room_role'] ?? ''));
    $profileInitial = strtoupper(substr($profileName, 0, 1));
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<link rel="stylesheet" href="'.$base.'Style/styles.css?v=56">';
    echo '<script type="importmap">'.json_encode(['imports' => ['three' => ($base === '' ? './' : $base) . 'assets/vendor/three/three.module.js']], JSON_UNESCAPED_SLASHES).'</script>';
    echo '<title>'.e($title).'</title></head>';
    echo '<body class="dashboard-page">';
    require __DIR__ . '/components/Dashboard/room_sidebar.php';
    echo '<main class="dashboard-content">';
    echo '<div class="dashboard-topline">';
    echo '<header class="dashboard-gallery-header"><span class="dashboard-gallery-eyebrow">TUP VISAYAS · CAMPUS ROOMS</span><h1>'.e($currentPage === 'room_index.php' ? 'Room Management System' : $title).'</h1></header>';
    echo '<div class="dashboard-topbar">';
    if (($_SESSION['room_role'] ?? '') === 'admin') {
        echo '<a class="dashboard-profile" href="'.$base.'components/Dashboard/room_account_edit.php?id='.$profileId.'" aria-label="Edit profile for '.e($profileName).'">';
    } else {
        echo '<div class="dashboard-profile dashboard-profile--static" aria-label="Signed in as '.e($profileName).'">';
    }
    echo '<span class="dashboard-profile-avatar" aria-hidden="true">'.e($profileInitial).'</span><span class="dashboard-profile-copy"><strong>'.e($profileName).'</strong><span>'.e($profileRole).'</span></span>';
    echo (($_SESSION['room_role'] ?? '') === 'admin' ? '</a>' : '</div>').'</div></div>';
    echo $body.'</main>';
    require __DIR__ . '/AI/room_chat_widget.php';
    echo '<script src="'.$base.'AI/chat.js?v=2" defer></script></body></html>';
}
