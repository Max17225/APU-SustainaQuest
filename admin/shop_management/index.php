<!-- admin/shop_management/index.php -->

<?php
// Shop Management Module Controller


/* =========================
   Top Bar Routing
   ========================= */
$top_bar_content = __DIR__ . '/top_bar/shop_management.php'; // get top bar content


/* =========================
   Read params
   ========================= */
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

    $main_section_content = __DIR__ . '/main_section/' . 'item_table.php';
}


/* =========================
   Render Layout
   ========================= */
// Load the layout(include: nav bar, top bar, main section)
require_once __DIR__ . '/../layout/layout.php';
// Load admin management script
require_once __DIR__ . '/../js/admin_management.php';