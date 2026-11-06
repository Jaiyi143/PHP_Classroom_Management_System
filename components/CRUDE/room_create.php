<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
$statuses = ['Vacant', 'Occupied'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['room_name'] ?? ''));
    $building = trim((string)($_POST['building'] ?? ''));
    $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $status = (string)($_POST['status'] ?? '');
    if ($name === '' || strlen($name) > 80 || $building === '' || strlen($building) > 60 || $capacity === false || !in_array($status, $statuses, true)) {
        $error = 'Check the room name, building, capacity and status.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO rooms (room_name, building, capacity, status) VALUES (?,?,?,?)');
            $stmt->execute([$name, $building, $capacity, $status]);
            header('Location: ../../room_index.php'); exit;
        } catch (PDOException $ex) {
            $error = 'Unable to save: the room name may already exist.';
        }
    }
}
ob_start(); ?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post">
<label>Room name <input name="room_name" maxlength="80" required></label>
<label>Building <input name="building" maxlength="60" required></label>
<label>Capacity <input type="number" name="capacity" min="0" required></label>
<label>Status <select name="status" required>
<?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
</select></label>
<button>Save room</button>
</form>
<?php page('Add Room', ob_get_clean());
