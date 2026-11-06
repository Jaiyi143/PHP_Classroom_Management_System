<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
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
    if ($name === '' || strlen($name) > 80 || $building === '' || strlen($building) > 60 || $capacity === false || !in_array($status, $statuses, true)) {
        $error = 'Please correct the fields.';
    } else {
        try {
            $update = $pdo->prepare('UPDATE rooms SET room_name = ?, building = ?, capacity = ?, status = ? WHERE room_id = ?');
            $update->execute([$name, $building, $capacity, $status, $id]);
            header('Location: ../../room_index.php'); exit;
        } catch (PDOException $ex) {
            $error = 'Could not save changes; the room name may already exist.';
        }
    }
    $room = ['room_name' => $name, 'building' => $building, 'capacity' => (int)$capacity, 'status' => $status];
}
ob_start(); ?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post">
<label>Room name <input name="room_name" maxlength="80" required value="<?= e($room['room_name']) ?>"></label>
<label>Building <input name="building" maxlength="60" required value="<?= e($room['building']) ?>"></label>
<label>Capacity <input type="number" name="capacity" min="0" required value="<?= (int)$room['capacity'] ?>"></label>
<label>Status <select name="status" required>
<?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $room['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
</select></label>
<button>Update room</button>
</form>
<?php page('Edit Room', ob_get_clean());
