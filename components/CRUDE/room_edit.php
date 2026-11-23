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
$buildings = roomBuildingOptions();
$legacyBuilding = in_array($room['building'], $buildings, true) ? null : $room['building'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['room_name'] ?? ''));
    $building = trim((string)($_POST['building'] ?? ''));
    $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $status = (string)($_POST['status'] ?? '');
    $imageFile = is_array($_FILES['room_image'] ?? null) ? $_FILES['room_image'] : null;
    $imageError = roomImageUploadError($imageFile);
    if ($name === '' || strlen($name) > 80 || !in_array($building, $buildings, true) || $capacity === false || !in_array($status, $statuses, true)) {
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
$currentImage = roomImageUrl($id);
ob_start(); ?>
<div class="dashboard-edit-overlay">
    <section class="dashboard-edit-modal" role="dialog" aria-modal="true" aria-labelledby="room-form-heading">
        <div class="room-modal-topbar"><span class="room-modal-eyebrow">EDIT ROOM</span><a class="room-modal-close" href="room_manage.php" aria-label="Close edit room">×</a></div>
        <div class="dashboard-edit-hero">
            <?php if ($currentImage): ?><img src="../../<?= e($currentImage) ?>" alt="Current image of <?= e($room['room_name']) ?>"><?php else: ?><span class="room-modal-placeholder">ROOM IMAGE</span><?php endif; ?>
        </div>
        <div class="dashboard-edit-content">
            <span class="room-modal-kicker">CAMPUS ROOM</span>
            <h2 id="room-form-heading"><?= e($room['room_name']) ?></h2>
            <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
            <form class="dashboard-editor-form dashboard-edit-form" method="post" enctype="multipart/form-data">
            <label>Room name <input name="room_name" maxlength="80" required value="<?= e($room['room_name']) ?>"></label>
            <label>Building <select name="building" required>
                <option value="" disabled <?= !in_array($room['building'], $buildings, true) ? 'selected' : '' ?>>Choose a building</option>
                <?php foreach ($buildings as $option): ?><option value="<?= e($option) ?>" <?= $room['building'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?>
            </select></label>
            <?php if ($legacyBuilding !== null): ?><p class="dashboard-field-note">Current building: <?= e($legacyBuilding) ?>. Choose one of the available buildings when updating this room.</p><?php endif; ?>
            <label>Capacity <input type="number" name="capacity" min="0" required value="<?= (int)$room['capacity'] ?>"></label>
            <label>Status <select name="status" required>
                <?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $room['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
            </select></label>
            <label class="dashboard-edit-field--wide"><?= $currentImage ? 'Replace room image' : 'Room image' ?> <input type="file" name="room_image" accept="image/jpeg,image/png,image/webp"></label>
            <p class="dashboard-field-note dashboard-edit-field--wide">Optional. JPG, PNG or WebP, up to 5 MB.</p>
            <div class="dashboard-edit-actions"><a href="room_manage.php">Cancel</a><button type="submit">Update room</button></div>
            </form>
        </div>
    </section>
</div>
<?php page('Edit Room', ob_get_clean());
