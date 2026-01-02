<?php
// moderator/profile_process.php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/db_connect.php";

if (session_status() === PHP_SESSION_NONE) session_start();

// Auth
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth_page/login.php");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'moderator') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mod_profile.php");
    exit();
}

$moderator_id = (int)$_SESSION['user_id'];

$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phoneNumber'] ?? '');

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

function back($msg, $type='success'){
    $_SESSION['status_msg']  = $msg;
    $_SESSION['status_type'] = $type;
    header("Location: mod_profile.php");
    exit();
}

if ($email === '' || $phone === '') {
    back("Email and phone number are required.", "warning");
}

try {
    $wants_password_change = ($current !== '' || $new !== '' || $confirm !== '');

    if ($wants_password_change) {
        if ($current === '' || $new === '' || $confirm === '') {
            back("To change password, fill in current + new + confirm.", "warning");
        }
        if ($new !== $confirm) {
            back("New password and confirm password do not match.", "warning");
        }
        if (strlen($new) < 6) {
            back("New password must be at least 6 characters.", "warning");
        }

        // Get current hash
        $stmt = $conn->prepare("SELECT modPassword FROM moderators WHERE moderatorId = ? LIMIT 1");
        $stmt->bind_param("i", $moderator_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($current, $row['modPassword'])) {
            back("Current password is incorrect.", "error");
        }

        $new_hash = password_hash($new, PASSWORD_DEFAULT);

        // Update contact + password
        $stmt = $conn->prepare("UPDATE moderators SET email = ?, phoneNumber = ?, modPassword = ? WHERE moderatorId = ?");
        $stmt->bind_param("sssi", $email, $phone, $new_hash, $moderator_id);
        $stmt->execute();
        $stmt->close();

        back("Profile updated and password changed successfully.", "success");
    } else {
        // Update only contact info
        $stmt = $conn->prepare("UPDATE moderators SET email = ?, phoneNumber = ? WHERE moderatorId = ?");
        $stmt->bind_param("ssi", $email, $phone, $moderator_id);
        $stmt->execute();
        $stmt->close();

        back("Profile updated successfully.", "success");
    }

} catch (Throwable $e) {
    back("Error updating profile: " . $e->getMessage(), "error");
}
