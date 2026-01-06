<!-- admin/admin_dashboard/index.php -->

<?php
// Admin dashboard Module Controller

/* =========================
   Top Bar Routing
   ========================= */
$top_bar_content = __DIR__ . '/top_bar/admin_dashboard.php'; // get top bar content


/* =========================
   Main Section Routing
   ========================= */
$pages = [
    'quest' => 'quest_report/index.php',
    'user'  => 'user_report.php',
    'mod'   => 'moderator_report.php',
    'shop'  => 'shop_report.php'
];

// default dashboard page
$page = $_GET['page'] ?? 'quest';

// get the content to display from admin_dashboard/main_section
$main_section_content = __DIR__ . '/main_section/' . ($pages[$page] ?? $pages['quest']);


/* =========================
   Render Layout
   ========================= */
// Load the layout(include: nav bar, top bar, main section)
require __DIR__ . '/../layout/layout.php';
// Load admin dashboard script
require __DIR__ . '/../js/admin_dashboard.php';
