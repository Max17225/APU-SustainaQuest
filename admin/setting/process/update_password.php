<?php
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/session_check.php';

session_start();
require_role('admin');

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) {
    header('Location: /APU-SustainaQuest/admin/?module=setting&error=password_mismatch');
    exit;
}

$stmt = $conn->prepare("SELECT adminPassword FROM admins WHERE adminId = 1");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!password_verify($current, $result['adminPassword'])) {
    header('Location: /APU-SustainaQuest/admin/?module=setting&error=wrong_password');
    exit;
}

$newHash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    UPDATE admins
    SET adminPassword = ?
    WHERE adminId = 1
");
$stmt->bind_param("s", $newHash);
$stmt->execute();

header('Location: /APU-SustainaQuest/admin/?module=setting&success=password_updated');
exit;