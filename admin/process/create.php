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