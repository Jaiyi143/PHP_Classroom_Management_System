<?php
require 'room_helpers.php';
requireLogin();
require 'room_config.php';
$search = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT room_id, room_name, building, capacity, status FROM rooms';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE room_name LIKE :q1 OR building LIKE :q2';
    $params = ['q1' => "%$search%", 'q2' => "%$search%"];
}
$sql .= ' ORDER BY room_id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$vacant = count(array_filter($rows, fn($r) => $r['status'] === 'Vacant'));
$occupied = count($rows) - $vacant;
ob_start();
?>
<form class="flex flex-wrap items-center gap-3" method="get"><label class="sr-only" for="room-search">Search rooms</label><input class="min-w-0 flex-1" id="room-search" name="q" placeholder="Search room or building" value="<?= e($search) ?>"><button type="submit">Search</button></form>
<p><span class="badge vacant"><?= $vacant ?> Vacant</span> <span class="badge occupied"><?= $occupied ?> Occupied</span></p>
<div class="table-wrap"><table><thead><tr><th>Room</th><th>Building</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['room_name']) ?></td><td><?= e($r['building']) ?></td>
<td><?= (int)$r['capacity'] ?></td>
<td><span class="badge <?= strtolower($r['status']) ?>"><?= e($r['status']) ?></span></td>
<td><a href="components/CRUDE/room_edit.php?id=<?= (int)$r['room_id'] ?>">Edit</a> · <a href="components/CRUDE/room_delete.php?id=<?= (int)$r['room_id'] ?>">Delete</a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<p><?= count($rows) ?> room(s) found</p>
<?php
page('Room Management System', ob_get_clean());
