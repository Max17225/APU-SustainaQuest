<!-- FILE: includes/session_check.php -->
<?php
require_once __DIR__ . '/general_function.php';

// Require user to be logged in to access a page
function require_login() {
    if (!isset($_SESSION['user_id'])) {

        $login_page = resolve_location('login.php');
        header("Location: $login_page");

        exit();
    }
}

// Require a specific user role to access a page, use this function after session_start().
// e.g., require_role('admin') for admin-only pages
// e.g., require_role('moderator') for moderator-only pages
// e.g., require_role('user') for normal user-only pages
function require_role(String $role): void 
{ 
    require_login();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {

        $login_page = resolve_location('login.php');
        header("Location: $login_page");

        exit();
    }
}