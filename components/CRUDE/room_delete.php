<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
requireAdminTeacher($pdo);
require __DIR__ . '/room_images.php';
$id = positiveId($_GET['id'] ?? null);
$stmt = $pdo->prepare('SELECT room_name FROM rooms WHERE room_id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); exit('Room not found.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('DELETE FROM rooms WHERE room_id = ?');
    $stmt->execute([$id]);
    deleteRoomImage($id);
    header('Location: room_manage.php'); exit;
}
ob_start(); ?>
<div class="dashboard-editor">
    <p class="dashboard-back"><a href="room_manage.php">← Back to rooms</a></p>
    <section class="dashboard-editor-card" aria-labelledby="delete-room-heading">
        <span class="account-eyebrow">DELETE ROOM</span>
        <h2 id="delete-room-heading"><?= e($row['room_name']) ?></h2>
        <p>Delete this room? This cannot be undone.</p>
        <div class="dashboard-editor-actions">
            <form method="post"><button class="danger" type="submit">Yes, delete this room</button></form>
            <a href="room_manage.php">Cancel</a>
        </div>
    </section>
</div>
<?php page('Confirm Delete', ob_get_clean());
