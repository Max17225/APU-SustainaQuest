<!-- auth_page/reset_password_process.php -->

<?php
session_start();
require_once '../includes/db_connect.php'; 
require_once '../includes/general_function.php';

/* =========================
   Request Method Check
   ========================= */
// Ensure the this process is accessed via the reset password form submission only
require_post('reset_password.php');

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

/* =========================
   Username and email existence check
   ========================= */
try {
    $target_user = null;
    $role = null;


    // Check in users table
    $stmt = $conn->prepare("SELECT userName, email FROM users WHERE userName = ? AND email = ?");

    $stmt->bind_param('ss', $username_input, $email_input);
    $stmt->execute();
    $result = $stmt->get_result();
    $target_user = $result->fetch_assoc();

    if ($target_user) {
        $role = 'user';

    } else {

        // Check in moderators table
        $stmt = $conn->prepare(
            "SELECT modName, email FROM moderators WHERE modName = ? AND email = ?"
        );
        $stmt->bind_param('ss', $username_input, $email_input);
        $stmt->execute();
        $result = $stmt->get_result();
        $target_user = $result->fetch_assoc();

        if ($target_user) {
            $role = 'moderator';
        }
    }

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
    redirect_with_status('Confirm passwords do not match.', 'warning', 'reset_password.php');
    exit;
}

/* =========================
   Update Password in Database
   ========================= */
try {
    // Hash password first
    $hashed_password = password_hash($new_password_input, PASSWORD_DEFAULT);

    if ($role === 'user') {

        $stmt = $conn->prepare(
            "UPDATE users 
            SET passwordHash = ? 
            WHERE userName = ? AND email = ?"
        );

    } elseif ($role === 'moderator') {

        $stmt = $conn->prepare(
            "UPDATE moderators 
            SET modPassword = ? 
            WHERE modName = ? AND email = ?"
        );
    }

    $stmt->bind_param(
        'sss',
        $hashed_password,
        $username_input,
        $email_input
    );

    $stmt->execute();

    // Check if any row was actually updated
    if ($stmt->affected_rows === 1) {
        // Successful password reset
        redirect_with_status('Password has been reset successfully.', 'success', 'login.php');
        exit;

    } else {
        // No rows updated 
        redirect_with_status('Failed to reset password. Please try again.', 'error', 'reset_password.php');
        exit;
    }
} catch (mysqli_sql_exception $e) {

    error_log('Password Reset Error: ' . $e->getMessage());
    redirect_with_status('Server error. Please try again later.', 'error', 'reset_password.php');
    exit;

} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}