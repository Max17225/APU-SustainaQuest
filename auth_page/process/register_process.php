<!-- auth_page/register_process.php -->

<?php
session_start();
require_once '../../includes/db_connect.php'; 
require_once '../../includes/general_function.php';

/* =========================
   Request Method Check
   ========================= */
// Ensure the this process is accessed via the registration form submission only
require_post('register.php');

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
// Special case: disallow "adam" as a username(the only reserved name for admin account)
if ($username_input === 'adam') {
    redirect_with_status('INVALID USERNAME "adam" - TRY ANOTHER USERNAME.', 'warning', 'register.php');
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
            redirect_with_status('Email already taken. Please choose another one.', 'warning', 'register.php');
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
    if ($stmt->execute()) {
        
        // Get the ID of the newly created user
        $new_user_id = $conn->insert_id;

        // ============================================================
        // AWARD DEFAULT 'Green Rookie' BADGE
        // ============================================================
        
        // 1. Get Badge ID
        $badge_sql = "SELECT badgeId FROM badges WHERE badgeName = 'Green Rookie' LIMIT 1";
        $badge_stmt = $conn->prepare($badge_sql);
        $badge_stmt->execute();
        $badge_result = $badge_stmt->get_result();

        if ($default_badge = $badge_result->fetch_assoc()) {
            // 2. Assign Badge to User
            $award_sql = "INSERT INTO userbadges (userId, badgeId) VALUES (?, ?)";
            $award_stmt = $conn->prepare($award_sql);
            $award_stmt->bind_param("ii", $new_user_id, $default_badge['badgeId']);
            $award_stmt->execute();
        }
    }


    redirect_with_status('Registration successful! You can now log in.', 'success', 'login.php');

} catch (mysqli_sql_exception $e) {
    redirect_with_status('Something went wrong. Please try again.', 'error', 'register.php');
    exit;

} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}