<?php
// admin/process/validate.php
// work with js 

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/_validator.php';

$type   = $_GET['type'] ?? '';
$value  = trim($_GET['value'] ?? '');
$entity = $_GET['entity'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

$error = null;

if ($type === 'username') {
    $error = validate_username($conn, $value, $entity, $id);
}

if ($type === 'email') {
    $error = validate_email($conn, $value, $entity, $id);
}

if ($type === 'questTitle') {
    $error = validate_quest_title($conn, $value, $id);
}

echo json_encode([
    'valid' => $error === null,
    'message' => $error
]);