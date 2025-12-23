<?php
session_start();
require_once '../includes/db_connect.php'; 
require_once '../includes/functions.php'; 

/* =========================
   Request Method Check
   ========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

/* =========================
   Input Sanitization
   ========================= */
$email_input    = trim($_POST['email'] ?? '');
$username_input = trim($_POST['username'] ?? '');
$password_input = $_POST['password'] ?? '';

/* =========================
   Basic Validation
   ========================= */
if ($username_input === '' || $password_input === '' || $email_input === '') {
    redirect_with_status('Please fill in all required fields.', 'warning', 'register.php');
    exit;
}

// Validate email format
if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('Invalid email format.', 'warning', 'register.php');
    exit;
}

// Username format validation (alphanumeric and underscores, 3-20 characters)
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username_input)) {
    redirect_with_status('Invalid username format.', 'warning', 'register.php');
    exit;
}

// Password strength validation (minimum 8 characters)
if (strlen($password_input) < 8) {
    redirect_with_status('Password must be at least 8 characters long.', 'warning', 'register.php');
    exit;
}

/* =========================
   Reserved Username (check for username conflicts)
   ========================= */
if ($username_input === 'adam') {
    redirect_with_status('The username "adam" is reserved. Please choose another.', 'warning', 'register.php');
    exit;
}

/* =========================
   Account Tables to Check for username and email conflicts (user and moderator)
   ========================= */
$accountTables = [
    ['table' => 'users',      'username' => 'userName', 'email' => 'email'],
    ['table' => 'moderators', 'username' => 'modName',  'email' => 'email']
];

try {
    foreach ($accountTables as $acc) {

        // Check username
        $sql  = "SELECT 1 FROM {$acc['table']} WHERE {$acc['username']} = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username_input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            redirect_with_status('Username already taken. Please choose another one.', 'warning', 'register.php');
            exit;
        }

        // Check email
        $sql  = "SELECT 1 FROM {$acc['table']} WHERE {$acc['email']} = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email_input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            redirect_with_status('An account with this email already exists.', 'warning', 'register.php');
            exit;
        }
    }

/*  =========================
    Insert new user
    ========================= */
    // Hash password
    $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);

    // Insert new user
    $sql = "INSERT INTO users (userName, email, passwordHash)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $username_input, $email_input, $hashedPassword);
    $stmt->execute();

    redirect_with_status('Registration successful! You can now log in.', 'success', 'login.php');

} catch (mysqli_sql_exception $e) {
    error_log('Registration Error: ' . $e->getMessage());
    redirect_with_status('An error occurred during registration. Please try again later.', 'error', 'register.php');
    exit;

}
?>