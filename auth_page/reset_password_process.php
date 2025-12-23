<?php
session_start();
require_once '../includes/db_connect.php'; 
require_once '../includes/functions.php'; 

/* =========================
   Request Method Check
   ========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reset_password.php');
    exit;
}

/* =========================
   Input Sanitization
   ========================= */
$email_input    = trim($_POST['email'] ?? '');
$username_input = trim($_POST['username'] ?? '');
$new_password_input = $_POST['new_password'] ?? '';
$confirm_password_input = $_POST['confirm_password'] ?? '';

/* =========================
   Basic Validation
   ========================= */
if ($username_input === '' || $email_input === '' || $new_password_input === '' || $confirm_password_input === '') {
    redirect_with_status('Please fill in all required fields.', 'warning', 'reset_password.php');
    exit;
}

// Validate email format
if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('Invalid email format.', 'warning', 'reset_password.php');
    exit;
}

/* =========================
   Username and email existence check
   ========================= */
try {
    // Prepare statement
    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE userName = ? AND email = ?"
    );

    // Bind parameters
    $stmt->bind_param('ss', $username_input, $email_input);
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();
    $target_user = $result->fetch_assoc();

    // Check if user exists
    if (!$target_user) {
        redirect_with_status('No account found with the provided username and email.', 'warning', 'reset_password.php');
        exit;
    }

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    redirect_with_status('Something went wrong. Please try again.', 'error', 'reset_password.php');
    exit;
}

/* =========================
   New Password format validation
   ========================= */
if (strlen($new_password_input) < 8) {
    redirect_with_status('Password must be at least 8 characters long.', 'warning', 'reset_password.php');
    exit;
}

/* =========================
   Password Confirmation Check
   ========================= */
if ($new_password_input !== $confirm_password_input) {
    redirect_with_status('New password and confirmation do not match.', 'warning', 'reset_password.php');
    exit;
}

/* =========================
   Update Password in Database
   ========================= */
try {
    // Hash password first
    $hashed_password = password_hash($new_password_input, PASSWORD_DEFAULT);

    // Prepare statement
    $stmt = $conn->prepare(
        "UPDATE users 
         SET passwordHash = ? 
         WHERE userName = ? AND email = ?"
    );

    // Bind parameters (3 params = 3 types)
    $stmt->bind_param(
        'sss',
        $hashed_password,
        $username_input,
        $email_input
    );

    $stmt->execute();

    // Check if update was successful
    if ($stmt->affected_rows === 1) {
        redirect_with_status('Password reset successfully. You can now log in.', 'success', 'login.php');
        exit;
    } else {
        redirect_with_status('Password reset failed. The new password might be the same as the old one.', 'warning', 'reset_password.php');
        exit;
    }
} catch (mysqli_sql_exception $e) {
    error_log('Password Reset Error: ' . $e->getMessage());
    redirect_with_status('Something went wrong during the password update. Please try again.', 'error', 'reset_password.php');
    exit;

} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}