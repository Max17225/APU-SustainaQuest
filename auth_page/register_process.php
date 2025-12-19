<?php
session_start();
require_once '../includes/db_connect.php'; 

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
    $_SESSION['status_msg']   = 'Please fill in all required fields.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: register.php');
    exit;
}

// Validate email format
if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['status_msg']   = 'Invalid email format.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: register.php');
    exit;
}

// Username format validation (alphanumeric and underscores, 3-20 characters)
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username_input)) {
    $_SESSION['status_msg']   = 'Invalid username format.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: register.php');
    exit;
}

// Password strength validation (minimum 8 characters)
if (strlen($password_input) < 8) {
    $_SESSION['status_msg']   = 'Password must be at least 8 characters long.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: register.php');
    exit;
}

/* =========================
   Reserved Username (check for username conflicts)
   ========================= */
// Special case: disallow "adam" as a username(the only reserved name for admin account)
if ($username_input === 'adam') {
    $_SESSION['status_msg']   = 'INVALID USERNAME "adam" - TRY ANOTHER USERNAME.';
    $_SESSION['status_class'] = 'status-warning';
    header('Location: register.php');
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
            $_SESSION['status_msg']   = 'Username already taken. Please choose another one.';
            $_SESSION['status_class'] = 'status-warning';
            header('Location: register.php');
            exit;
        }

        // Check email
        $sql  = "SELECT 1 FROM {$acc['table']} WHERE {$acc['email']} = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email_input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['status_msg']   = 'Email already taken. Please choose another one.';
            $_SESSION['status_class'] = 'status-warning';
            header('Location: register.php');
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

    $_SESSION['status_msg']   = 'Registration successful! You can now log in.';
    $_SESSION['status_class'] = 'status-success';
    header('Location: login.php');

} catch (mysqli_sql_exception $e) {
    $_SESSION['status_msg']   = 'An error occurred during registration. Please try again later.';
    $_SESSION['status_class'] = 'status-error';
    header('Location: register.php');
    exit;

}
?>