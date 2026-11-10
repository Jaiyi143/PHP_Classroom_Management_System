<?php
require __DIR__ . '/../AI/room_ai.php';
require __DIR__ . '/../room_helpers.php';

function check(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$buildings = ['Engineering Building', 'Library'];
$_SERVER['SCRIPT_NAME'] = '/app/AI/room_ai_search.php';
check(roomBasePath() === '../', 'AI page assets should resolve from the parent directory');
$_SERVER['SCRIPT_NAME'] = '/app/components/Dashboard/room_accounts.php';
check(roomBasePath() === '../../', 'Existing component assets should keep their path');
$criteria = roomAiParseCriteria('Find a vacant room for 30 students in Engineering Building', $buildings);
check($criteria['status'] === 'Vacant' && $criteria['min_capacity'] === 30 && $criteria['building'] === 'Engineering Building', 'Room request should be parsed locally');
check($criteria['recognized'] === true && $criteria['unsupported'] === false, 'Room request should be recognized');
check(roomAiParseCriteria('What is available tomorrow?', $buildings)['unsupported'] === true, 'Future availability should be declined');
check(roomAiParseCriteria('Tell me a joke', $buildings)['recognized'] === false, 'Unrelated question should not return every room');
check(roomAiParseCriteria('Find a room for 0 students', $buildings)['recognized'] === false, 'Zero capacity should not return every room');
echo "room_ai_test: OK\n";
