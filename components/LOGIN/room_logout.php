<?php
declare(strict_types=1);

session_start();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    $cookieOptions = [
        'expires' => time() - 3600,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
    ];
    if (!empty($params['samesite'])) {
        $cookieOptions['samesite'] = $params['samesite'];
    }
    setcookie(session_name(), '', $cookieOptions);
}

session_destroy();
header('Location: room_login.php');
exit;
