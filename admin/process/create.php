<?php
// admin/process/create.php

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/session_check.php';

session_start();
require_role('admin');

$entity = $_POST['entity'] ?? '';

/* =========================
   CREATE USER
   ========================= */
if ($entity === 'user') {

    $stmt = $conn->prepare("
        INSERT INTO users (userName, email, passwordHash, level, greenPoints)
        VALUES (?, ?, ?, ?, ?)
    ");

    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt->bind_param(
        "sssii",
        $_POST['userName'],
        $_POST['email'],
        $passwordHash,
        $_POST['level'],
        $_POST['greenPoints']
    );

    $stmt->execute();

    header("Location: ../?module=user&page=user");
    exit;
}

/* =========================
   CREATE MODERATOR
   ========================= */
if ($entity === 'mod') {

    $stmt = $conn->prepare("
        INSERT INTO moderators (modName, modPassword, email, phoneNumber)
        VALUES (?, ?, ?, ?)
    ");

    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt->bind_param(
        "ssss",
        $_POST['modName'],
        $passwordHash,
        $_POST['email'],
        $_POST['phoneNumber']
    );

    $stmt->execute();

    header("Location: ../?module=user&page=mod");
    exit;
}

/* =========================
   CREATE QUEST
   ========================= */
if ($entity === 'quest') {

    $iconPath = null;

    if (!empty($_FILES['questIcon']['name'])) {

        $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/APU-SustainaQuest/assets/image/quests/';
        // save directory if not 
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $ext = pathinfo($_FILES['questIcon']['name'], PATHINFO_EXTENSION); // extracts jpg, png...
        $fileName = uniqid('quest_', true) . '.' . $ext; // generate a unique name

        $fullPath = $baseDir . $fileName;

        // move to target folder
        move_uploaded_file($_FILES['questIcon']['tmp_name'], $fullPath);

        // URL value saved into DB
        $iconPath = 'assets/image/quests/' . $fileName;
    }

    $stmt = $conn->prepare("
        INSERT INTO quests
            (createdByAdminId, title, description, pointReward, expReward, type, questIconURL, isActive)
        VALUES (1, ?, ?, ?, ?, ?, ?, 0)
    ");

    $stmt->bind_param(
        "ssiiss",
        $_POST['title'],
        $_POST['description'],
        $_POST['pointReward'],
        $_POST['expReward'],
        $_POST['questType'],
        $iconPath
    );

    $stmt->execute();

    header("Location: ../?module=quest&page=available");
    exit;
}