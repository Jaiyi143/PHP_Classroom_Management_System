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
$greeting = 'Hello! I can help you find rooms by building, capacity, or current availability. What room are you looking for?';
foreach (['hi', 'Hello!', 'hellow', 'hey there', 'good morning'] as $message) {
    checkFaq(roomAiFaqAnswer($message) === $greeting, "Greeting should be answered: $message");
}
checkFaq(roomAiFaqAnswer('Hi, find a vacant room') === null, 'Greeting before a room request should not hide the request');
echo "room_ai_faq_test: OK\n";
