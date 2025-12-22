<<<<<<< HEAD
<?php
// FILE: includes/session_check.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Internal helper to get normalized session data.
 * Handles both 'user_id'/'role' and legacy 'is_logged_in'/'user_role' keys.
 */
function _get_session_data() {
    $is_logged_in = isset($_SESSION['is_logged_in']) ? $_SESSION['is_logged_in'] : isset($_SESSION['user_id']);
    $user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['role']) ? $_SESSION['role'] : null);
    return [$is_logged_in, $user_role];
}

// --- Specific Role Checks (Used by local files) ---

/**
 * Checks if the user is logged in and has the Moderator role.
 * If not, redirects to the login page.
 */
function check_moderator_access() {
    list($is_logged_in, $user_role) = _get_session_data();
    if (!$is_logged_in || $user_role !== 'moderator') {
        header('Location: ../auth_page/login.php');
        exit();
    }
}

function check_admin_access() {
    list($is_logged_in, $user_role) = _get_session_data();
    if (!$is_logged_in || $user_role !== 'admin') {
        header('Location: ../auth_page/login.php');
        exit();
    }
}

function check_user_access() {
    list($is_logged_in, $user_role) = _get_session_data();
    if (!$is_logged_in) {
        header('Location: ../auth_page/login.php');
        exit();
    }
}

// --- Generic Role Checks (From main branch) ---

function require_login() {
    check_user_access();
}

function require_role(string $role): void {
    list($is_logged_in, $user_role) = _get_session_data();
    if (!$is_logged_in || $user_role !== $role) {
        header('Location: ../auth_page/login.php');
        exit();
    }
}
?>
