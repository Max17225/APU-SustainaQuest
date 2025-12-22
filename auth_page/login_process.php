<!-- auth_page/login_process.php -->

<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/general_function.php'; 

/* =========================
   Request Method Check
   ========================= */
// Ensure the this process is accessed via the login form submission only
require_post('login.php');

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
    redirect_with_status('Please fill in all required fields.', 'warning', 'login.php');
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
                userId AS id,
                userName AS username,
                passwordHash AS password,
                'user' AS role,
                isBanned AS isBanned
            FROM users

            UNION ALL

            SELECT
                adminId AS id,
                adminName AS username,
                adminPassword AS password,
                'admin' AS role,
                0 AS isBanned
            FROM admins

            UNION ALL

            SELECT
                moderatorId AS id,
                modName AS username,
                modPassword AS password,
                'moderator' AS role,
                0 AS isBanned
            FROM moderators
        ) AS all_accounts
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $username_input); 
    $stmt->execute();

    $result = $stmt->get_result();
    $account = $result->fetch_assoc(); // Fetch data as an associative array

    // Verify user existence and password correctness
    if (!$account || !password_verify($password_input, $account['password'])) {
        redirect_with_status('Invalid username or password.', 'warning', 'login.php');
        exit;
    }

    // Check if the normal user is banned
    if ($account['role'] === 'user' && $account['isBanned']) {
        redirect_with_status('Account has been banned.', 'warning', 'login.php');
        exit;
    }

    // Successful login - set session variables
    $_SESSION['role'] = $account['role'];
    $_SESSION['user_id'] = $account['id'];

    $admin_dashboard = resolve_location('admin_dashboard.php');
    $mod_dashboard   = resolve_location('mod_dashboard.php');
    $user_dashboard  = resolve_location('user_dashboard.php');
    switch ($account['role']) {

        case 'admin':
            header("Location: $admin_dashboard"); // Redirect to the homepage u did
            exit;

        case 'moderator':
            header("Location: $mod_dashboard"); // Redirect to the homepage u did
            exit;

        default:
            header("Location: $user_dashboard"); // Redirect to the homepage u did
    }

} catch (mysqli_sql_exception $e) {
    // Log the error message for debugging (avoid displaying sensitive info to users)
    error_log('Database Error: ' . $e->getMessage());
    redirect_with_status('Server error. Please try again later.', 'error', 'login.php');
    exit;
    
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
