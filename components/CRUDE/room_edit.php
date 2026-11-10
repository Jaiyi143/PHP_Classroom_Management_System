<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
requireAdminTeacher($pdo);
require __DIR__ . '/room_images.php';
$id = positiveId($_GET['id'] ?? null);
$stmt = $pdo->prepare('SELECT * FROM rooms WHERE room_id = ?');
$stmt->execute([$id]);
$room = $stmt->fetch();
if (!$room) { http_response_code(404); exit('Room not found.'); }
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
        $error = 'Please correct the fields.';
    } elseif ($imageError !== '') {
        $error = $imageError;
    } else {
        try {
            $update = $pdo->prepare('UPDATE rooms SET room_name = ?, building = ?, capacity = ?, status = ? WHERE room_id = ?');
            $update->execute([$name, $building, $capacity, $status, $id]);
            if (!saveRoomImage($id, $imageFile)) {
                $error = 'Room details were saved, but the image could not be saved.';
            } else {
                header('Location: room_manage.php'); exit;
            }
        } catch (PDOException $ex) {
            $error = 'Could not save changes; the room name may already exist.';
        }
    }
    $room = ['room_name' => $name, 'building' => $building, 'capacity' => (int)$capacity, 'status' => $status];
}
ob_start(); ?>
<div class="dashboard-editor">
    <p class="dashboard-back"><a href="room_manage.php">← Back to rooms</a></p>
    <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <section class="dashboard-editor-card" aria-labelledby="room-form-heading">
        <span class="account-eyebrow">EDIT ROOM</span>
        <h2 id="room-form-heading"><?= e($room['room_name']) ?></h2>
        <form class="dashboard-editor-form" method="post" enctype="multipart/form-data">
            <label>Room name <input name="room_name" maxlength="80" required value="<?= e($room['room_name']) ?>"></label>
            <label>Building <input name="building" maxlength="60" required value="<?= e($room['building']) ?>"></label>
            <label>Capacity <input type="number" name="capacity" min="0" required value="<?= (int)$room['capacity'] ?>"></label>
            <label>Status <select name="status" required>
                <?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $room['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
            </select></label>
            <?php $currentImage = roomImageUrl($id); ?>
            <?php if ($currentImage): ?><div class="dashboard-current-image"><img class="room-edit-image" src="../../<?= e($currentImage) ?>" alt="Current image of <?= e($room['room_name']) ?>"></div><?php endif; ?>
            <label><?= $currentImage ? 'Replace room image' : 'Room image' ?> <input type="file" name="room_image" accept="image/jpeg,image/png,image/webp"></label>
            <p class="dashboard-field-note">Optional. JPG, PNG or WebP, up to 5 MB.</p>
            <button type="submit">Update room</button>
        </form>
    </section>
</div>
<?php page('Edit Room', ob_get_clean());
