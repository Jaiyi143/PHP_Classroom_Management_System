<?php
function roomBasePath(): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_contains($script, '/components/CRUDE/') ? '../../' : '';
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
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<link rel="stylesheet" href="'.$base.'Style/styles.css?v=11"><title>'.e($title).'</title></head><body>';
    echo '<main>';
    require __DIR__ . '/room_nav.php';
    echo '<h1>'.e($title).'</h1>'.$body.'</main></body></html>';
}
