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
$_SESSION['room_ai_csrf'] ??= bin2hex(random_bytes(32));

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
<form class="dashboard-search" method="get" role="search">
    <?php if ($statusFilter !== ''): ?>
        <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
    <?php endif; ?>
    <?php if ($buildingFilter !== ''): ?>
        <input type="hidden" name="building" value="<?= e($buildingFilter) ?>">
    <?php endif; ?>
    <label class="sr-only" for="room-search">Search rooms</label>
    <input id="room-search" name="q" type="search" placeholder="Search room or building" value="<?= e($search) ?>">
    <button type="submit">Search</button>
</form>
<div class="room-chat" id="room-ai">
    <section class="room-chat-panel" id="room-chat-panel" aria-labelledby="room-chat-heading" data-room-ai-panel>
        <header class="room-chat-header">
            <h2 id="room-chat-heading">Now chatting</h2>
            <button class="room-chat-close" type="button" aria-label="Minimize chat" aria-controls="room-chat-panel" data-room-ai-toggle>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 9 7 7 7-7"/></svg>
            </button>
        </header>
        <div class="room-chat-messages" role="log" aria-label="Room assistant conversation" aria-live="polite" data-room-ai-messages>
            <div class="room-chat-message room-chat-message--assistant">
                <span class="room-chat-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 5h14v11H9l-4 3V5Z"/><path d="M9 10h.01M12 10h.01M15 10h.01"/></svg></span>
                <div><span class="room-chat-sender">Room Assistant</span><p>Hi! Ask me about room availability, building, or capacity.</p></div>
            </div>
        </div>
        <form class="room-chat-form" method="post" action="AI/room_ai_search.php" data-room-ai-form>
            <input type="hidden" name="csrf" value="<?= e($_SESSION['room_ai_csrf']) ?>">
            <label class="sr-only" for="room-ai-question">Message the room assistant</label>
            <input id="room-ai-question" name="question" type="text" maxlength="500" required autocomplete="off" placeholder="Type here and hit enter to chat">
            <button type="submit" aria-label="Send message"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 12 18-9-5 18-4-7-9-2Z"/><path d="m12 14 9-11"/></svg></button>
        </form>
    </section>
    <button class="room-chat-launcher" type="button" aria-controls="room-chat-panel" aria-expanded="true" data-room-ai-launcher>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v11H9l-5 3V5Z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>
        <span>Room Chat</span>
    </button>
</div>
<script src="AI/chat.js" defer></script>
<div class="dashboard-summary" aria-label="Room availability summary">
    <div class="dashboard-summary-card"><span>Total rooms</span><strong><?= array_sum($counts) ?></strong></div>
    <div class="dashboard-summary-card dashboard-summary-card--vacant"><span>Vacant</span><strong><?= $counts['Vacant'] ?></strong></div>
    <div class="dashboard-summary-card dashboard-summary-card--occupied"><span>Occupied</span><strong><?= $counts['Occupied'] ?></strong></div>
</div>
<section class="dashboard-filters" aria-labelledby="filter-heading">
    <h2 id="filter-heading">Filter rooms</h2>
    <div class="dashboard-filter-group">
        <h3 class="room-filter-heading">Status</h3>
        <nav class="room-filter-tabs" aria-label="Filter rooms by status">
            <a class="room-filter-link" href="<?= e($filterUrl('', $buildingFilter)) ?>" <?= $statusFilter === '' ? 'aria-current="page"' : '' ?>>All rooms <span class="room-filter-count"><?= array_sum($counts) ?></span></a>
            <a class="room-filter-link room-filter-link--vacant" href="<?= e($filterUrl('Vacant', $buildingFilter)) ?>" <?= $statusFilter === 'Vacant' ? 'aria-current="page"' : '' ?>>Vacant <span class="room-filter-count"><?= $counts['Vacant'] ?></span></a>
            <a class="room-filter-link" href="<?= e($filterUrl('Occupied', $buildingFilter)) ?>" <?= $statusFilter === 'Occupied' ? 'aria-current="page"' : '' ?>>Occupied <span class="room-filter-count"><?= $counts['Occupied'] ?></span></a>
        </nav>
    </div>
    <div class="dashboard-filter-group">
        <h3 class="room-filter-heading">Building</h3>
        <nav class="room-filter-tabs" aria-label="Filter rooms by building">
            <a class="room-filter-link" href="<?= e($filterUrl($statusFilter, '')) ?>" <?= $buildingFilter === '' ? 'aria-current="page"' : '' ?>>All buildings</a>
            <?php foreach ($buildingOptions as $building): ?>
                <a class="room-filter-link" href="<?= e($filterUrl($statusFilter, $building)) ?>" <?= $buildingFilter === $building ? 'aria-current="page"' : '' ?>><?= e($building) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>

<section class="dashboard-gallery" aria-labelledby="gallery-heading">
<h2 id="gallery-heading">Explore rooms</h2>
<?php if ($rooms === []): ?>
    <p class="dashboard-empty">No rooms found. Try a different search or filter.</p>
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
                    <span class="badge <?= strtolower($room['status']) ?> dashboard-card-status"><?= e($room['status']) ?></span>
                    <span class="room-card-view" aria-hidden="true">View room</span>
                </div>
                <div class="room-card-details">
                    <span class="room-card-kicker">ROOM</span>
                    <h3><?= e($room['room_name']) ?></h3>
                    <p class="room-card-description"><?= e($room['building']) ?></p>
                    <div class="room-card-footer"><span>Capacity <strong><?= (int) $room['capacity'] ?></strong></span><span class="dashboard-card-action">View room <span aria-hidden="true">↗</span></span></div>
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
                            <span class="room-modal-placeholder">ROOM IMAGE</span>
                        <?php endif; ?>
                    </div>
                    <div class="room-modal-content">
                        <span class="room-modal-kicker">CAMPUS ROOM</span>
                        <h2 id="room-title-<?= (int) $room['room_id'] ?>"><?= e($room['room_name']) ?></h2>
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
</section>
<?php page($statusFilter !== '' ? $statusFilter . ' Rooms' : 'Room Management System', ob_get_clean());
