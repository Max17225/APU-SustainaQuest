<?php
session_start();

/* =========================
   Destroy Session
   ========================= */
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

/* =========================
   Redirect to login
   ========================= */
require_once __DIR__ . '/../includes/general_function.php';
header("Location: " . resolve_location('login.php'));
exit;