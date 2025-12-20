<?php
// FILE: includes/functions.php
// General helper functions used across the application

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitizes user input to prevent XSS and potential injection issues.
 * @param string $data The raw input string.
 * @return string The sanitized string.
 */
function sanitize_input(string $data): string {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

?>
