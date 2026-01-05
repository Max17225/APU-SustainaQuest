<?php
// check_limited_available.php

require_once __DIR__ . '/../../../includes/db_connect.php';

$stmt = $conn->query("
    SELECT COUNT(*) AS total
    FROM items
    WHERE itemType = 'Limited' AND availableStatus = 1
");

$count = $stmt->fetch_assoc()['total'] ?? 0;

echo json_encode([
    'allowed' => $count < 8
]);