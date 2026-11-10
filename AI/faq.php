<?php

// Add your own question => answer pairs to this list.
function roomAiFaqs(): array {
    return [
        'Can I reserve a room?' => 'Reservations are not available in this system. You can view each room’s current status on the dashboard.',
        'How do I find a vacant room?' => 'Use the Vacant filter on the dashboard, or ask me to find a vacant room in a specific building or with a certain capacity.',
        'Can you check availability tomorrow?' => 'I can only check the current Vacant or Occupied status. This system does not store future room schedules.',
    ];
}

function roomAiFaqAnswer(string $question): ?string {
    $normalize = static function (string $text): string {
        $text = mb_strtolower(trim($text), 'UTF-8');
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '');
    };
    $question = $normalize($question);
    foreach (roomAiFaqs() as $savedQuestion => $answer) {
        if ($question === $normalize($savedQuestion)) return $answer;
    }
    return null;
}
