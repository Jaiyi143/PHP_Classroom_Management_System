<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
requireAdminTeacher($pdo);
require __DIR__ . '/room_images.php';
$statuses = ['Vacant', 'Occupied'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['room_name'] ?? ''));
    $building = trim((string)($_POST['building'] ?? ''));
    $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $status = (string)($_POST['status'] ?? '');
    $imageFile = is_array($_FILES['room_image'] ?? null) ? $_FILES['room_image'] : null;
    $imageError = roomImageUploadError($imageFile);
    if ($name === '' || strlen($name) > 80 || $building === '' || strlen($building) > 60 || $capacity === false || !in_array($status, $statuses, true)) {
        $error = 'Check the room name, building, capacity and status.';
    } elseif ($imageError !== '') {
        $error = $imageError;
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO rooms (room_name, building, capacity, status) VALUES (?,?,?,?)');
            $stmt->execute([$name, $building, $capacity, $status]);
            if (!saveRoomImage((int) $pdo->lastInsertId(), $imageFile)) {
                $pdo->rollBack();
                $error = 'The room image could not be saved.';
            } else {
                $pdo->commit();
                header('Location: room_manage.php'); exit;
            }
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = 'Unable to save: the room name may already exist.';
        }
    }
}
ob_start(); ?>
<div class="dashboard-editor">
    <p class="dashboard-back"><a href="room_manage.php">← Back to rooms</a></p>
    <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <section class="dashboard-editor-card" aria-labelledby="room-form-heading">
        <span class="account-eyebrow">NEW ROOM</span>
        <h2 id="room-form-heading">Room details</h2>
        <form class="dashboard-editor-form" method="post" enctype="multipart/form-data">
            <label>Room name <input name="room_name" maxlength="80" required></label>
            <label>Building <input name="building" maxlength="60" required></label>
            <label>Capacity <input type="number" name="capacity" min="0" required></label>
            <label>Status <select name="status" required>
                <?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
            </select></label>
            <label>Room image <input type="file" name="room_image" accept="image/jpeg,image/png,image/webp"></label>
            <p class="dashboard-field-note">Optional. JPG, PNG or WebP, up to 5 MB.</p>
            <button type="submit">Save room</button>
        </form>
    </section>
</div>
<?php page('Add Room', ob_get_clean());
