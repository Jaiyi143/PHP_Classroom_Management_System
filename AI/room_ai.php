<?php

function roomAiParseCriteria(string $question, array $buildings): array {
    $text = mb_strtolower($question, 'UTF-8');
    $unsupported = preg_match('/\b(tomorrow|next week|next month|future|schedule|reservation|reserve|booking|book|equipment|projector)\b/u', $text) === 1;
    $status = null;
    if (preg_match('/\b(vacant|available|free)\b/u', $text)) $status = 'Vacant';
    if (preg_match('/\b(occupied|in use)\b/u', $text)) $status = 'Occupied';
    $capacity = null;
    if (preg_match('/\b(?:for|at least|minimum(?: of)?|fit|fits|seat|seats)\s+(\d{1,4})\b/u', $text, $matches)) {
        if ((int) $matches[1] > 0) $capacity = (int) $matches[1];
    }
    $buildingMatch = null;
    foreach ($buildings as $building) {
        $shortName = preg_replace('/ Building$/i', '', $building);
        if (mb_stripos($question, $building, 0, 'UTF-8') !== false || mb_stripos($question, $shortName, 0, 'UTF-8') !== false) {
            $buildingMatch = $building;
            break;
        }
    }
    return [
        'status' => $status,
        'min_capacity' => $capacity,
        'building' => $buildingMatch,
        'unsupported' => $unsupported,
        'recognized' => $status !== null || $capacity !== null || $buildingMatch !== null,
    ];
}

function roomAiFind(PDO $pdo, array $criteria): array {
    $conditions = [];
    $params = [];
    if ($criteria['status'] !== null) { $conditions[] = 'status = :status'; $params['status'] = $criteria['status']; }
    if ($criteria['min_capacity'] !== null) { $conditions[] = 'capacity >= :capacity'; $params['capacity'] = $criteria['min_capacity']; }
    if ($criteria['building'] !== null) { $conditions[] = 'building = :building'; $params['building'] = $criteria['building']; }
    $sql = 'SELECT room_id, room_name, building, capacity, status FROM rooms';
    if ($conditions !== []) $sql .= ' WHERE ' . implode(' AND ', $conditions);
    $sql .= ' ORDER BY room_id LIMIT 30';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
