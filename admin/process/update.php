<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/session_check.php';

session_start();
require_role('admin');

$entity = $_POST['entity'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$password = trim($_POST['password'] ?? '');

if (!$id) exit;

if ($entity === 'user') {

    if ($password !== '') {
        // with password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE users
            SET userName=?, email=?, passwordHash=?, level=?, greenPoints=?, isBanned=?
            WHERE userId=?
        ");
        $stmt->bind_param(
            "sssiiii",
            $_POST['userName'],
            $_POST['email'],
            $hash,
            $_POST['level'],
            $_POST['greenPoints'],
            $_POST['isBanned'],
            $id
        );
    } else {
        // Without password
        $stmt = $conn->prepare("
            UPDATE users
            SET userName=?, email=?, level=?, greenPoints=?, isBanned=?
            WHERE userId=?
        ");
        $stmt->bind_param(
            "ssiiii",
            $_POST['userName'],
            $_POST['email'],
            $_POST['level'],
            $_POST['greenPoints'],
            $_POST['isBanned'],
            $id
        );
    }

    $stmt->execute();
    header("Location: ../?module=user&page=user");
    exit;
}

if ($entity === 'mod') {

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE moderators
            SET modName=?, email=?, modPassword=?, phoneNumber=?
            WHERE moderatorId=?
        ");
        $stmt->bind_param(
            "ssssi",
            $_POST['modName'],
            $_POST['email'],
            $hash,
            $_POST['phoneNumber'],
            $id
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE moderators
            SET modName=?, email=?, phoneNumber=?
            WHERE moderatorId=?
        ");
        $stmt->bind_param(
            "sssi",
            $_POST['modName'],
            $_POST['email'],
            $_POST['phoneNumber'],
            $id
        );
    }

    $stmt->execute();
    header("Location: ../?module=user&page=mod");
    exit;
}