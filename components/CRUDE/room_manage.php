<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
requireAdminTeacher($pdo);
require __DIR__ . '/room_images.php';

$search = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT room_id, room_name, building, capacity, status FROM rooms';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE room_name LIKE :name OR building LIKE :building';
    $params = ['name' => "%$search%", 'building' => "%$search%"];
}
$sql .= ' ORDER BY room_id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

ob_start();
?>
<form class="dashboard-search" method="get" role="search">
    <label class="sr-only" for="manage-search">Search rooms</label>
    <input id="manage-search" name="q" type="search" placeholder="Search room or building" value="<?= e($search) ?>">
    <button type="submit">Search</button>
</form>
<section class="dashboard-gallery room-management-gallery" aria-labelledby="manage-gallery-heading">
    <div class="dashboard-gallery-toolbar">
        <h2 id="manage-gallery-heading">Rooms</h2>
        <a class="dashboard-create-link" href="room_create.php">Add room</a>
    </div>
<?php if ($rooms === []): ?>
    <p class="dashboard-empty">No rooms found. Try a different search.</p>
<?php else: ?>
    <div class="room-card-grid">
        <?php foreach ($rooms as $room): ?>
            <?php $imageUrl = roomImageUrl((int) $room['room_id']); ?>
            <article class="room-card">
                <div class="room-card-image">
                    <?php if ($imageUrl !== null): ?>
                        <img src="../../<?= e($imageUrl) ?>" alt="<?= e($room['room_name']) ?>" loading="lazy">
                    <?php else: ?>
                        <span class="room-card-placeholder">ROOM IMAGE</span>
                    <?php endif; ?>
                    <span class="badge <?= strtolower($room['status']) ?> dashboard-card-status"><?= e($room['status']) ?></span>
                </div>
                <div class="room-card-details">
                    <span class="room-card-kicker">ROOM</span>
                    <h3><?= e($room['room_name']) ?></h3>
                    <p class="room-card-description"><?= e($room['building']) ?></p>
                    <div class="room-card-footer">
                        <span>Capacity <strong><?= (int) $room['capacity'] ?></strong></span>
                        <div class="room-manage-actions">
                            <a href="room_edit.php?id=<?= (int) $room['room_id'] ?>">Edit room</a>
                            <a class="delete-link" href="room_delete.php?id=<?= (int) $room['room_id'] ?>">Delete</a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</section>
<?php page('Edit Rooms', ob_get_clean());
