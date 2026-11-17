<?php
require __DIR__ . '/../room_helpers.php';

$_SESSION['room_logged_in'] = true;
$_SESSION['room_user_id'] = 1;
$_SESSION['room_username'] = 'Tester';
$_SESSION['room_role'] = 'student';

foreach (['/room_index.php' => './', '/components/CRUDE/room_manage.php' => '../../'] as $script => $base) {
    $_SERVER['SCRIPT_NAME'] = $script;
    ob_start();
    page('Test', '<p>Content</p>');
    $html = ob_get_clean();
    if (!str_contains($html, '"three":"' . $base . 'assets/vendor/three/three.module.js"')) {
        throw new RuntimeException("Three.js import map path missing on $script");
    }
}

foreach (['three.module.js', 'three.core.js', 'LICENSE'] as $file) {
    if (!is_file(__DIR__ . '/../assets/vendor/three/' . $file)) {
        throw new RuntimeException("Three.js browser module missing: $file");
    }
}
echo "three_import_test: OK\n";
