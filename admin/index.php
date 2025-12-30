<!-- admin/index.php -->

<?php
// Entry Point
require_once __DIR__ . '/../includes/db_connect.php';

require_once __DIR__ . '/../includes/session_check.php';

/* =========================
   Auth & Role Check
   ========================= */
session_start();
require_role('admin');

/* =========================
   Module Routing
   ========================= */

// allowed admin modules
$modules = [
    'dashboard' => 'admin_dashboard',
    'user'     => 'user_management',
    'quest'    => 'quest_management',
    'shop'      => 'shop_management',
    'setting'  => 'setting'
];

// default module
$module = $_GET['module'] ?? 'dashboard';

if (!array_key_exists($module, $modules)) {
    $module = 'dashboard';
}

// load module inside this file
require __DIR__ . '/' . $modules[$module] . '/index.php';