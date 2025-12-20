<?php
session_start();
require_once '../includes/db_connect.php'; 

/* =========================
   Request Method Check
   ========================= */
// Ensure the this process is accessed via the login form submission only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

/* =========================
   Input Sanitization
   ========================= */
$username_input = trim($_POST['username'] ?? '');
$password_input  = $_POST['password'] ?? '';

/* =========================
   Basic Validation
   ========================= */
// Server-side validation to prevent empty form submission
// Client-side HTML validation like required can be bypassed by disabling JavaScript or sending a manual POST request
// If any required field is empty, redirect the user back to the login page
if ($username_input === '' || $password_input === '') {
    $_SESSION['status_msg'] = 'Please fill in all required fields.';
    $_SESSION['status_class']   = 'status-warning';

    header('Location: login.php');
    exit;
}

/* =========================
    User Authentication
   ========================= */
try {
    // Prepare and execute the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("
        SELECT *
        FROM (
            SELECT
                moderatorId AS id,
                modName AS username,
                email AS email,
                modPassword AS password,
                'moderator' AS role,
                0 AS isBanned
            FROM moderators

            UNION ALL

            SELECT
                adminId AS id,
                adminName AS username,
                NULL AS email,
                adminPassword AS password,
                'admin' AS role,
                0 AS isBanned
            FROM admins

            UNION ALL

            SELECT 
                userId AS id,
                userName AS username,
                email AS email,
                passwordHash AS password,
                'user' AS role,
                isBanned AS isBanned
            FROM users
        ) AS all_accounts
        WHERE username = ? OR email = ?
        LIMIT 1
    ");

    $stmt->bind_param("ss", $username_input, $username_input);
    $stmt->execute();

    $result = $stmt->get_result();
    $account = $result->fetch_assoc(); // Fetch data as an associative array

    // Verify user existence and password correctness
    if (!$account || !password_verify($password_input, $account['password'])) {
        $_SESSION['status_msg'] = 'Invalid username or password.';
        $_SESSION['status_class']   = 'status-warning';

        header('Location: login.php');
        exit;
    }

    // Check if the normal user is banned
    if ($account['role'] === 'user' && $account['isBanned']) {
        $_SESSION['status_msg'] = 'The user account has been banned.';
        $_SESSION['status_class']   = 'status-error';

        header('Location: login.php');
        exit;
    }

    // Successful login - set session variables
    $_SESSION['role'] = $account['role'];
    $_SESSION['user_id'] = $account['id'];

    switch ($account['role']) {
        case 'admin':
            header('Location: ../admin/admin_dashboard.php'); // Redirect to the homepage u did
            exit;

        case 'moderator':
            header('Location: ../moderator/mod_dashboard.php'); // Redirect to the homepage u did
            exit;

        default:
            header('Location: ../user/user_dashboard.php'); // Redirect to the homepage u did
    }

} catch (mysqli_sql_exception $e) {
    // Log the error message for debugging (avoid displaying sensitive info to users)
    error_log('Database Error: ' . $e->getMessage());
    $_SESSION['status_msg'] = 'Server error. Please try again later.';
    $_SESSION['status_class']   = 'status-error';

    header('Location: login.php');
    exit;
    
}
?>