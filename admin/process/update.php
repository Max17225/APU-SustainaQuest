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

// Change questDelete Table, drop the selected record to make the quest available again
if ($_POST['entity'] === 'quests' && isset($_POST['restore'])) {

    $id = (int)$_POST['id'];

    // restore quest
    $conn->query("DELETE FROM questDelete WHERE questId = $id");

    header("Location: ../?module=quest&page=available");
    exit;
}

if ($_POST['entity'] == 'quests') {
    $id           = (int)$_POST['id'];
    $title        = trim($_POST['title']);
    $description  = trim($_POST['description']);
    $pointReward  = (int)$_POST['pointReward'];
    $expReward    = (int)$_POST['expReward'];
    $type         = $_POST['questType'];

    // ---------- ICON (OPTIONAL) ----------
    $iconSQL = '';
    $params  = [$title, $description, $pointReward, $expReward, $type];
    $types   = "ssiss";

    if (!empty($_FILES['questIcon']['name'])) {
        $ext = pathinfo($_FILES['questIcon']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('quest_') . '.' . $ext;

        $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/APU-SustainaQuest/assets/image/quests/';
        $fullPath = $baseDir . $filename;

        move_uploaded_file($_FILES['questIcon']['tmp_name'], $fullPath);
        $iconPath = 'assets/image/quests/' . $filename;

        $iconSQL = ", questIconURL = ?";
        $params[] = $iconPath;
        $types .= "s";
    }

    $params[] = $id;
    $types .= "i";

    // ---------- UPDATE ----------
    $sql = "
        UPDATE quests
        SET title = ?, description = ?, pointReward = ?, expReward = ?, type = ?
        $iconSQL
        WHERE questId = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    header("Location: ../?module=quest&page=available");
    exit;
}