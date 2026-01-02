<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/session_check.php';

session_start();
require_role('admin');

$data = json_decode(file_get_contents('php://input'), true);

$entity = $data['entity'] ?? null;
$ids    = $data['ids'] ?? [];

if (!$entity || !is_array($ids) || empty($ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

/* =========================
   Entity whitelist
   ========================= */
$map = [
    'user' => ['table' => 'users', 'pk' => 'userId'],
    'mod'  => ['table' => 'moderators', 'pk' => 'moderatorId'],
    'quest'=> ['table' => 'quests', 'pk' => 'questId'],
    'shop' => ['table' => 'items', 'pk' => 'itemId']
];

if (!isset($map[$entity])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$table = $map[$entity]['table'];
$pk    = $map[$entity]['pk'];

/* =========================
   Delete
   ========================= */
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = $conn->prepare("DELETE FROM {$table} WHERE {$pk} IN ($placeholders)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();

echo json_encode(['success' => true]);