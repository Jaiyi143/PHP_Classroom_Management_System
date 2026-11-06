<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
$id = positiveId($_GET['id'] ?? null);
$stmt = $pdo->prepare('SELECT room_name FROM rooms WHERE room_id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); exit('Room not found.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('DELETE FROM rooms WHERE room_id = ?');
    $stmt->execute([$id]);
    header('Location: ../../room_index.php'); exit;
}
ob_start(); ?>
<p>Delete <strong><?= e($row['room_name']) ?></strong>? This cannot be undone.</p>
<form method="post"><button class="danger">Yes, delete this room</button></form>
<a href="../../room_index.php">Cancel</a>
<?php page('Confirm Delete', ob_get_clean());
