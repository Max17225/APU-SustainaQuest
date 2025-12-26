<!-- admin/layout/config.php -->

<?php
/* =========================
   Admin Layout Config
   ========================= */

return [

    /* ---------- Basic ---------- */
    'html' => [
        'lang'  => 'en',
        'title' => 'Admin Panel'
    ],

    /* ---------- Theme ---------- */
    'theme' => [
        'default'   => 'dark',
        'available' => ['dark', 'light']
    ],

    /* ---------- CSS ---------- */
    'css' => [
        'base' => [
            'CSS/admin.css',
        ],
        // %s will be replaced by theme name
        'theme' => 'CSS/theme/%s.css'
    ],

    /* ---------- Nav Bar ---------- */    
    // module corresponds to the 'module' GET parameter (inside admin/index.php)
    'nav' => [
        ['label' => 'Dashboard',            'module' => 'dashboard'],
        ['label' => 'User Management',      'module' => 'user'],
        ['label' => 'Quests Management',    'module' => 'quest'],
        ['label' => 'Shop Management',      'module' => 'shop'],
        ['label' => 'Settings',             'module' => 'setting'],
    ]

];
