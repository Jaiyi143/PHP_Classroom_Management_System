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
<form class="mb-5 flex flex-wrap items-center gap-3" method="get">
    <label class="sr-only" for="manage-search">Search rooms</label>
    <input class="min-w-0 flex-1" id="manage-search" name="q" placeholder="Search room or building" value="<?= e($search) ?>">
    <button type="submit">Search</button>
</form>
<div class="mb-5 flex justify-end">
    <a class="inline-block rounded-md bg-brand px-5 py-3 font-semibold text-white no-underline hover:bg-brand-strong hover:text-white" href="room_create.php">Add room</a>
</div>
<?php if ($rooms === []): ?>
    <p>No rooms found.</p>
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
                </div>
                <div class="room-card-details">
                    <span class="room-card-kicker">ROOM</span>
                    <h2><?= e($room['room_name']) ?></h2>
                    <p class="room-card-description"><?= e($room['building']) ?> · Capacity <?= (int) $room['capacity'] ?></p>
                    <div class="room-card-footer">
                        <span class="badge <?= strtolower($room['status']) ?>"><?= e($room['status']) ?></span>
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
<?php page('Edit Rooms', ob_get_clean());
