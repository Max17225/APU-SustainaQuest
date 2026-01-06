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
// Boolean Variable to help layout.php decide to load the script
$load_management_script = true;
$load_shop_management_script = true;

if ($action === 'edit' || $action === 'create') {
   $load_form_script = true;
   $load_shop_form_script = true;
   $load_management_script = false;
   $load_shop_management_script = false;
}

// Load the layout(include: nav bar, top bar, main section)
require_once __DIR__ . '/../layout/layout.php';
