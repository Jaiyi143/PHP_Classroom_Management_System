<?php
require __DIR__ . '/../room_helpers.php';

$_SESSION['room_logged_in'] = true;
$_SESSION['room_user_id'] = 1;
$_SESSION['room_username'] = 'Tester';
$_SESSION['room_role'] = 'student';

foreach (['/room_index.php' => '', '/components/CRUDE/room_manage.php' => '../../', '/AI/room_ai_search.php' => '../'] as $script => $base) {
    $_SERVER['SCRIPT_NAME'] = $script;
    ob_start();
    page('Test', '<p>Content</p>');
    $html = ob_get_clean();
    if (substr_count($html, 'data-room-ai-panel') !== 1) throw new RuntimeException("Chat widget missing or duplicated on $script");
    if (!str_contains($html, 'action="' . $base . 'AI/room_ai_search.php"')) throw new RuntimeException("Chat form path wrong on $script");
    if (!str_contains($html, 'src="' . $base . 'AI/chat.js')) throw new RuntimeException("Chat script path wrong on $script");
}
echo "chat_widget_test: OK\n";
