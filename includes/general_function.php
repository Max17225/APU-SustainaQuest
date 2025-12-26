<?php

// Function to get image paths based on a param
// param: string $key - the key representing the image(like 'logo')
// return: string - the corresponding image path
function image_path(string $key): string
{
    $map = [
        'logo' => '/APU-GreenPoint-System/assets/image/SustainaQuest Logo.png',
    ];

    return $map[$key];
}   

// Function to resolve page locations based on a param
// param: string $page - the key representing the page(like 'login.php')
// return: string - the corresponding page location
function resolve_location(string $page): string
{
    $page = trim($page);

    $routes = [
        'login.php'          => '/APU-GreenPoint-System/auth_page/login.php',
        'register.php'       => '/APU-GreenPoint-System/auth_page/register.php',
        'reset_password.php' => '/APU-GreenPoint-System/auth_page/reset_password.php',
        'logout.php'         => '/APU-GreenPoint-System/includes/logout.php',
        'user_dashboard.php' => '/APU-GreenPoint-System/user/user_dashboard.php',
        'admin_dashboard.php'=> '/APU-GreenPoint-System/admin/admin_dashboard.php',
        'mod_dashboard.php'  => '/APU-GreenPoint-System/moderator/mod_dashboard.php',
        'admin'              => '/APU-GreenPoint-System/admin/',
    ];

    if (!isset($routes[$page])) {
        throw new InvalidArgumentException("Unknown route key: {$page}");
    }    

    return $routes[$page];
}

// Function to ensure the request method is POST
// param: string $redirectPage - the page to redirect to if not POST
// return: void
function require_post(string $redirectPage): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        try {
            $location = resolve_location($redirectPage);

        } catch (InvalidArgumentException $e) {
            throw $e;
        }

        header("Location: {$location}");
        exit;
    }
}

// Function to set session status message and redirect
// param: string $msg - the status message to display
// param: string $status - the status type('warning', 'error', 'success')
// param: string $location - the location to redirect to
// return: void
function redirect_with_status(string $msg, string $status, string $page): void
{
    $status = trim(strtolower($status));

    // Map status types
    $statusMap = [
        'warning' => 'status-warning',
        'error'   => 'status-error',
        'success' => 'status-success',
    ];

    $_SESSION['status_msg']   = $msg;
    $_SESSION['status_class'] = $statusMap[$status] ?? 'status-error';

    $location = resolve_location($page);

    header("Location: $location");
    exit;
}