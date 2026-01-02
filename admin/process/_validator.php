<?php
// admin/process/_validator.php

function validate_username($conn, $username, $entity, $id = 0) {

    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        return '3–20 characters, letters, numbers, underscore only';
    }

    if (strtolower($username) === 'adam') {
        return 'Username reserved';
    }

    // users
    $sql = "SELECT 1 FROM users WHERE userName = ?";
    if ($entity === 'user' && $id) $sql .= " AND userId != ?";
    $stmt = $conn->prepare($sql);
    $entity === 'user' && $id
        ? $stmt->bind_param("si", $username, $id)
        : $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) return 'Username already exists';

    // moderators
    $sql = "SELECT 1 FROM moderators WHERE modName = ?";
    if ($entity === 'mod' && $id) $sql .= " AND moderatorId != ?";
    $stmt = $conn->prepare($sql);
    $entity === 'mod' && $id
        ? $stmt->bind_param("si", $username, $id)
        : $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) return 'Username already exists';

    return null;
}

function validate_email($conn, $email, $entity, $id = 0) {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format';
    }

    $sql = "SELECT 1 FROM users WHERE email = ?";
    if ($entity === 'user' && $id) $sql .= " AND userId != ?";
    $stmt = $conn->prepare($sql);
    $entity === 'user' && $id
        ? $stmt->bind_param("si", $email, $id)
        : $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) return 'Email already in use';

    $sql = "SELECT 1 FROM moderators WHERE email = ?";
    if ($entity === 'mod' && $id) $sql .= " AND moderatorId != ?";
    $stmt = $conn->prepare($sql);
    $entity === 'mod' && $id
        ? $stmt->bind_param("si", $email, $id)
        : $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) return 'Email already in use';

    return null;
}

function validate_password(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters';
    }

    return null;
}
