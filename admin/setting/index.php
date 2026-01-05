<!-- admin/setting/index.php -->

<?php
// Setting Module Controller


/* =========================
   Top Bar Routing
   ========================= */
$top_bar_content = __DIR__ . '/top_bar/setting.php'; // get top bar content


/* =========================
   Main Secition Routing
   ======================== */
$main_section_content = __DIR__ . '/main_section/' . 'setting_option.php';

/* =========================
   Render Layout
   ========================= */
// Load the layout(include: nav bar, top bar, main section)
require __DIR__ . '/../layout/layout.php';
