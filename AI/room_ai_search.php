<?php
require __DIR__ . '/../room_helpers.php';
requireLogin();
require __DIR__ . '/../room_config.php';
require __DIR__ . '/room_ai.php';
require __DIR__ . '/faq.php';

$question = trim((string) ($_POST['question'] ?? ''));
$faqAnswer = roomAiFaqAnswer($question);
$error = '';
$rooms = [];
$unsupported = false;
$recognized = false;
$wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../room_index.php'); exit;
}
if (empty($_SESSION['room_ai_csrf']) || !hash_equals($_SESSION['room_ai_csrf'], (string) ($_POST['csrf'] ?? ''))) {
    http_response_code(403); $error = 'Please return to the dashboard and try again.';
} elseif ($question === '' || strlen($question) > 500) {
    $error = 'Enter a question of up to 500 characters.';
} elseif ($faqAnswer === null) {
    try {
        $buildings = $pdo->query('SELECT DISTINCT building FROM rooms ORDER BY building')->fetchAll(PDO::FETCH_COLUMN);
        $criteria = roomAiParseCriteria($question, $buildings);
        $unsupported = $criteria['unsupported'];
        $recognized = $criteria['recognized'];
        if (!$unsupported && $recognized) $rooms = roomAiFind($pdo, $criteria);
    } catch (Throwable $exception) {
        error_log('Room chat search failed: ' . $exception->getMessage());
        $error = 'Room search is temporarily unavailable. Please use the regular room search.';
    }
}

$message = $error !== ''
    ? $error
    : ($faqAnswer ?? ($unsupported
        ? 'I can search current room status, building, and capacity. I cannot check future schedules, equipment, or make reservations.'
        : (!$recognized
            ? 'I can answer or search rooms by current status, building, and capacity. Try “Find a vacant room for 30 students in the Engineering Building.”'
            : ($rooms === []
                ? 'No rooms match that request. Try another building, capacity, or status.'
                : 'These rooms match the current records:'))));

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => $message, 'rooms' => $rooms], JSON_THROW_ON_ERROR);
    exit;
}

ob_start();
?>
<section class="room-ai-result" aria-labelledby="room-ai-result-heading">
    <a href="../room_index.php#room-ai">← Back to dashboard</a>
    <h2 id="room-ai-result-heading">Room assistant</h2>
    <p><strong>Your question:</strong> <?= e($question) ?></p>
    <p<?= $error !== '' ? ' role="alert"' : '' ?>><?= e($message) ?></p>
    <?php if ($error === '' && !$unsupported && $rooms !== []): ?>
        <ul>
            <?php foreach ($rooms as $room): ?>
                <li><a href="../room_index.php#room-<?= (int) $room['room_id'] ?>"><?= e($room['room_name']) ?></a> — <?= e($room['building']) ?>, capacity <?= (int) $room['capacity'] ?>, <?= e($room['status']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php page('Room assistant', ob_get_clean());
