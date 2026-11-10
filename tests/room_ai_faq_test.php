<?php
require __DIR__ . '/../AI/faq.php';

function checkFaq(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

checkFaq(
    roomAiFaqAnswer('  CAN I RESERVE A ROOM???  ') === 'Reservations are not available in this system. You can view each room’s current status on the dashboard.',
    'A saved question should match despite case, spacing, and punctuation'
);
checkFaq(roomAiFaqAnswer('What is the weather?') === null, 'Unknown questions should continue to AI search');
echo "room_ai_faq_test: OK\n";
