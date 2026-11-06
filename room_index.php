<?php
require __DIR__ . '/room_helpers.php';
requireLogin();
require __DIR__ . '/room_config.php';
require __DIR__ . '/components/CRUDE/room_images.php';

$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');
if (!in_array($statusFilter, ['Vacant', 'Occupied'], true)) {
    $statusFilter = '';
}
$buildingOptions = ['MET Building', 'Engineering Building', 'Library'];
$buildingFilter = (string) ($_GET['building'] ?? '');
if (!in_array($buildingFilter, $buildingOptions, true)) {
    $buildingFilter = '';
}

$conditions = [];
$params = [];
if ($buildingFilter !== '') {
    $conditions[] = 'building = :building_filter';
    $params['building_filter'] = $buildingFilter;
}
if ($search !== '') {
    $conditions[] = '(room_name LIKE :name OR building LIKE :building_search)';
    $params['name'] = "%$search%";
    $params['building_search'] = "%$search%";
}
$counts = ['Vacant' => 0, 'Occupied' => 0];
$countSql = 'SELECT status, COUNT(*) AS total FROM rooms';
if ($conditions !== []) {
    $countSql .= ' WHERE ' . implode(' AND ', $conditions);
}
$countStmt = $pdo->prepare($countSql . ' GROUP BY status');
$countStmt->execute($params);
foreach ($countStmt as $count) {
    $counts[$count['status']] = (int) $count['total'];
}
if ($statusFilter !== '') {
    $conditions[] = 'status = :status';
    $params['status'] = $statusFilter;
}
$sql = 'SELECT room_id, room_name, building, capacity, status FROM rooms';
if ($conditions !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY room_id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$filterUrl = static function (string $status, string $building) use ($search): string {
    $query = [];
    if ($status !== '') {
        $query['status'] = $status;
    }
    if ($building !== '') {
        $query['building'] = $building;
    }
    if ($search !== '') {
        $query['q'] = $search;
    }

    return 'room_index.php' . ($query !== [] ? '?' . http_build_query($query) : '');
};

ob_start();
?>
<form class="mb-6 flex flex-wrap items-center gap-3" method="get">
    <?php if ($statusFilter !== ''): ?>
        <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
    <?php endif; ?>
    <?php if ($buildingFilter !== ''): ?>
        <input type="hidden" name="building" value="<?= e($buildingFilter) ?>">
    <?php endif; ?>
    <label class="sr-only" for="room-search">Search rooms</label>
    <input class="min-w-0 flex-1" id="room-search" name="q" placeholder="Search room or building" value="<?= e($search) ?>">
    <button type="submit">Search</button>
</form>
<p class="room-filter-heading">Status</p>
<nav class="room-filter-tabs" aria-label="Filter rooms by status">
    <a class="room-filter-link" href="<?= e($filterUrl('', $buildingFilter)) ?>" <?= $statusFilter === '' ? 'aria-current="page"' : '' ?>>All rooms <span class="room-filter-count"><?= array_sum($counts) ?></span></a>
    <a class="room-filter-link room-filter-link--vacant" href="<?= e($filterUrl('Vacant', $buildingFilter)) ?>" <?= $statusFilter === 'Vacant' ? 'aria-current="page"' : '' ?>>Vacant <span class="room-filter-count"><?= $counts['Vacant'] ?></span></a>
    <a class="room-filter-link" href="<?= e($filterUrl('Occupied', $buildingFilter)) ?>" <?= $statusFilter === 'Occupied' ? 'aria-current="page"' : '' ?>>Occupied <span class="room-filter-count"><?= $counts['Occupied'] ?></span></a>
</nav>
<p class="room-filter-heading">Building</p>
<nav class="room-filter-tabs" aria-label="Filter rooms by building">
    <a class="room-filter-link" href="<?= e($filterUrl($statusFilter, '')) ?>" <?= $buildingFilter === '' ? 'aria-current="page"' : '' ?>>All buildings</a>
    <?php foreach ($buildingOptions as $building): ?>
        <a class="room-filter-link" href="<?= e($filterUrl($statusFilter, $building)) ?>" <?= $buildingFilter === $building ? 'aria-current="page"' : '' ?>><?= e($building) ?></a>
    <?php endforeach; ?>
</nav>

<?php if ($rooms === []): ?>
    <p>No rooms found.</p>
<?php else: ?>
    <div class="room-card-grid" id="rooms">
        <?php foreach ($rooms as $room): ?>
            <?php $imageUrl = roomImageUrl((int) $room['room_id']); ?>
            <article class="room-card">
                <a class="room-card-link" href="#room-<?= (int) $room['room_id'] ?>" aria-label="View <?= e($room['room_name']) ?> details">
                <div class="room-card-image">
                    <?php if ($imageUrl !== null): ?>
                        <img src="<?= e($imageUrl) ?>" alt="<?= e($room['room_name']) ?>" loading="lazy">
                    <?php else: ?>
                        <span class="room-card-placeholder">ROOM IMAGE</span>
                    <?php endif; ?>
                    <span class="room-card-view" aria-hidden="true">View room</span>
                </div>
                <div class="room-card-details">
                    <span class="room-card-kicker">ROOM</span>
                    <h2><?= e($room['room_name']) ?></h2>
                    <p class="room-card-description"><?= e($room['building']) ?> · Capacity <?= (int) $room['capacity'] ?></p>
                    <div class="room-card-footer"><span class="badge <?= strtolower($room['status']) ?>"><?= e($room['status']) ?></span></div>
                </div>
                </a>
            </article>
            <section class="room-modal" id="room-<?= (int) $room['room_id'] ?>" aria-labelledby="room-title-<?= (int) $room['room_id'] ?>">
                <div class="room-modal-panel">
                    <div class="room-modal-topbar">
                        <span class="room-modal-eyebrow">ROOM DETAILS</span>
                        <a class="room-modal-close" href="#rooms" aria-label="Close room details">×</a>
                    </div>
                    <div class="room-modal-image">
                        <?php if ($imageUrl !== null): ?>
                            <img src="<?= e($imageUrl) ?>" alt="<?= e($room['room_name']) ?>">
                        <?php else: ?>
                            <span>ROOM IMAGE</span>
                        <?php endif; ?>
                    </div>
                    <div class="room-modal-content">
                        <h2 id="room-title-<?= (int) $room['room_id'] ?>"><?= e($room['room_name']) ?></h2>
                        <p>This room is in <?= e($room['building']) ?> and has capacity for <?= (int) $room['capacity'] ?> people.</p>
                        <dl class="room-modal-facts">
                            <div><dt>BUILDING</dt><dd><?= e($room['building']) ?></dd></div>
                            <div><dt>CAPACITY</dt><dd><?= (int) $room['capacity'] ?> people</dd></div>
                            <div><dt>STATUS</dt><dd><span class="badge <?= strtolower($room['status']) ?>"><?= e($room['status']) ?></span></dd></div>
                        </dl>
                        <div class="room-modal-footer"><a href="#rooms">Back to rooms</a></div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php page($statusFilter !== '' ? $statusFilter . ' Rooms' : 'Room Management System', ob_get_clean());
