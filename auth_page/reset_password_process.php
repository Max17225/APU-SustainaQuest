<?php
session_start();
require_once '../includes/db_connect.php'; 

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
    $_SESSION['status_msg']   = 'Please fill in all required fields.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: reset_password.php');
    exit;
}

// Validate email format
if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['status_msg']   = 'Invalid email format.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: reset_password.php');
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
        $_SESSION['status_msg']   = 'No account found with the provided username and email.';
        $_SESSION['status_class'] = 'status-warning';
        header('Location: reset_password.php');
        exit;
    }

} catch (mysqli_sql_exception $e) {

    error_log($e->getMessage());
    $_SESSION['status_msg']   = 'Something went wrong. Please try again.';
    $_SESSION['status_class'] = 'status-error';
    header('Location: reset_password.php');
    exit;
}

/* =========================
   New Password format validation
   ========================= */
if (strlen($new_password_input) < 8) {
    $_SESSION['status_msg']   = 'Password must be at least 8 characters long.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: reset_password.php');
    exit;
}

/* =========================
   Password Confirmation Check
   ========================= */
if ($new_password_input !== $confirm_password_input) {
    $_SESSION['status_msg']   = 'New password and confirmation do not match.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: reset_password.php');
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
        $_SESSION['status_msg']   = 'Password reset successfully.';
        $_SESSION['status_class'] = 'status-success';
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['status_msg']   = 'Password reset failed or no changes made.';
        $_SESSION['status_class'] = 'status-warning';
        header('Location: reset_password.php');
        exit;
    }
} catch (mysqli_sql_exception $e) {

    error_log('Password Reset Error: ' . $e->getMessage());
    $_SESSION['status_msg']   = 'Something went wrong. Please try again.';
    $_SESSION['status_class'] = 'status-error';
    header('Location: reset_password.php');
    exit;

} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}