<?php
// admin/shop_management/process/get_item_count.php

require_once __DIR__ . '/../../../includes/db_connect.php';

$type = $_GET['type'] ?? '';

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM items
    WHERE itemType = ?
");
$stmt->bind_param("s", $type);
$stmt->execute();

$count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

echo json_encode(['count' => (int)$count]);

