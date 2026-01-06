<!-- admin/quest_management/index.php -->

<?php
// Quest Management Module Controller

/* =========================
   Normalize URL (default page)
   ========================= */
if (!isset($_GET['page'])) {
    header('Location: ?module=quest&page=available');
    exit;
}


/* =========================
   Top Bar Routing
   ========================= */
$top_bar_content = __DIR__ . '/top_bar/quest_management.php'; // get top bar content


/* =========================
   Read params
   ========================= */
$page   = $_GET['page'];
$action = $_GET['action'] ?? null;


/* =========================
   Action Routing (highest priority)
   ========================= */
if ($action === 'create') {
    $main_section_content = __DIR__ . '/main_section/create_form.php';
}
elseif ($action === 'edit') {
    $main_section_content = __DIR__ . '/main_section/edit_form.php';
}
else {
    /* =========================
       Page Routing (fallback)
       ========================= */
    $pages = [
        'available' => 'quest_table.php',
        'deleted'  => 'deleted_quest_table.php'
    ];

    $file = $pages[$page] ?? $pages['available'];
    $main_section_content = __DIR__ . '/main_section/' . $file;
}


/* =========================
   Render Layout
   ========================= */
// Load the layout(include: nav bar, top bar, main section)
require_once __DIR__ . '/../layout/layout.php';
// Load admin management script
require_once __DIR__ . '/../js/admin_management.php';
