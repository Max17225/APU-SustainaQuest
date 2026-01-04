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

if ($entity === 'items') {

    $id = (int)$_POST['id'];


    //   Fetch existing item
    $stmt = $conn->prepare("
        SELECT itemPictureURL, itemType
        FROM items
        WHERE itemId = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();

    if (!$current) {
        die("Item not found");
    }

    $itemType = $current['itemType']; // IMMUTABLE
    $picturePath = $current['itemPictureURL'];

    // Image upload
    if (!empty($_FILES['itemPicture']['name'])) {

        $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/APU-SustainaQuest/assets/image/items/';

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $ext = pathinfo($_FILES['itemPicture']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('item_', true) . '.' . $ext;
        $fullPath = $baseDir . $fileName;

        move_uploaded_file($_FILES['itemPicture']['tmp_name'], $fullPath);

        $picturePath = 'assets/image/items/' . $fileName;
    }

    // Available Process (Limited only)
    $availableStatus = 0;

    if ($itemType === 'Limited') {

        $availableStatus = isset($_POST['availableStatus'])
            ? (int)$_POST['availableStatus']
            : 0;

        // limit check
        if ($availableStatus === 1) {

            $check = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM items
                WHERE itemType = 'Limited'
                  AND availableStatus = 1
                  AND itemId != ?
            ");
            $check->bind_param("i", $id);
            $check->execute();
            $count = $check->get_result()->fetch_assoc()['total'];

            if ($count >= 8) {
                header("Location: ../?module=shop&action=edit&id={$id}");
                exit;
            }
        }

    } else {
        // Permanent items never toggle availability
        $availableStatus = $current['availableStatus'] ?? 1;
    }

    // update item detail
    $stmt = $conn->prepare("
        UPDATE items
        SET
            itemName = ?,
            itemDesc = ?,
            itemPictureURL = ?,
            quantity = ?,
            pointCost = ?,
            availableStatus = ?
        WHERE itemId = ?
    ");

    $stmt->bind_param(
        "sssiiii",
        $_POST['itemName'],
        $_POST['itemDesc'],
        $picturePath,
        $_POST['quantity'],
        $_POST['pointCost'],
        $availableStatus,
        $id
    );

    $stmt->execute();

    header("Location: ../?module=shop");
    exit;
}