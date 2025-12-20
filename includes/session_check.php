<?php
// FILE: includes/session_check.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if the user is logged in and has the Moderator role.
 * If not, redirects to the login page.
 */
function check_moderator_access() {
    // Support both older session keys ('is_logged_in'/'user_role') and
    // the current login process keys ('user_id'/'role'). Normalize for checks.
    $is_logged_in = isset($_SESSION['is_logged_in']) ? $_SESSION['is_logged_in'] : isset($_SESSION['user_id']);
    $user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['role']) ? $_SESSION['role'] : null);

    if (!$is_logged_in || $user_role !== 'moderator') {
        header('Location: ../auth_page/login.php');
        exit();
    }
}

function check_admin_access() {
    $is_logged_in = isset($_SESSION['is_logged_in']) ? $_SESSION['is_logged_in'] : isset($_SESSION['user_id']);
    $user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['role']) ? $_SESSION['role'] : null);
    if (!$is_logged_in || $user_role !== 'admin') {
        header('Location: ../auth_page/login.php');
        exit();
    }
}

function check_user_access() {
    $is_logged_in = isset($_SESSION['is_logged_in']) ? $_SESSION['is_logged_in'] : isset($_SESSION['user_id']);
    if (!$is_logged_in) {
        header('Location: ../auth_page/login.php');
        exit();
    }
}

?>
