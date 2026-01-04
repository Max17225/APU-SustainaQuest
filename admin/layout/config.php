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
        'default'   => 'Dark',
        'available' => ['Dark', 'Fridge']
    ],

    /* ---------- CSS ---------- */
    'css' => [
        'base' => [
            'CSS/admin.css',
        ],
        
        'theme' => 'CSS/theme/%s.css'
    ],

    /* ---------- Nav Bar ---------- */    
    // module corresponds to the 'module' GET parameter (inside admin/index.php)
    'nav' => [
        [
            'label'  => 'Dashboard',
            'module' => 'dashboard',
            'svg'    => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4"><path d="M22.454 21.748a1.5 1.5 0 0 1-1.5 1.5H3a1.5 1.5 0 0 1-1.5-1.5v-19.5A1.5 1.5 0 0 1 3 .748h15a1.5 1.5 0 0 1 1.047.426L22 4.057a1.5 1.5 0 0 1 .452 1.074z"/><path d="M5.204 9.748a5.25 5.25 0 1 0 10.5 0a5.25 5.25 0 0 0-10.5 0"/><path d="M5.205 9.748h5.23l1.146 5.129m-1.146-5.129l3.732-3.712m4.537 10.462h-3m3 3h-6.75"/></g></svg>',   // dashboard icon
        ],
        [
            'label'  => 'User Management',
            'module' => 'user',
            'svg'    => '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 500 500"><path fill="currentColor" fill-rule="evenodd" d="M341.336 117.333c0 41.237-33.43 74.667-74.667 74.667s-74.666-33.43-74.666-74.667s33.429-74.666 74.666-74.666s74.667 33.429 74.667 74.666m-170.667 64c0 29.455-23.878 53.334-53.333 53.334s-53.333-23.879-53.333-53.334S87.88 128 117.336 128s53.333 23.878 53.333 53.333M42.669 320c0-35.346 28.654-64 64-64h21.334c2.578 0 5.122.152 7.621.449c-4.913 12.278-7.624 25.738-7.624 39.852v109.032H42.67zm290.249-64h59.26v22.837a88.8 88.8 0 0 1 28.127 16.267l19.804-11.433l29.629 51.32l-19.793 11.427a89.4 89.4 0 0 1 1.482 16.247a89.4 89.4 0 0 1-1.482 16.247l19.794 11.428l-29.63 51.32l-19.804-11.434a88.8 88.8 0 0 1-28.127 16.266v22.841h-59.26v-22.834a88.8 88.8 0 0 1-28.141-16.268l-19.791 11.426l-29.629-51.32l19.775-11.417a89.4 89.4 0 0 1-1.483-16.255c0-5.552.509-10.985 1.483-16.255l-19.775-11.417l29.63-51.32l19.789 11.426a88.8 88.8 0 0 1 28.142-16.269zm65.175 106.667c0 19.637-15.918 35.556-35.555 35.556s-35.556-15.919-35.556-35.556s15.919-35.555 35.556-35.555s35.555 15.918 35.555 35.555m-248.76-64.001c0-47.128 38.205-85.333 85.334-85.333h64c21.753 0 41.605 8.14 56.677 21.54c-67.284 3.796-120.675 59.56-120.675 127.793c0 14.961 2.567 29.322 7.284 42.667h-92.62z" clip-rule="evenodd"/></svg>',   // user icon
        ],
        [
            'label'  => 'Quests Management',
            'module' => 'quest',
            'svg'    => '',   
        ],
        [
            'label'  => 'Shop Management',
            'module' => 'shop',
            'svg'    => '',   
        ],
        [
            'label'  => 'Settings',
            'module' => 'setting',
            'svg'    => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 48 48"><defs><mask id="SVGO7QlTCPD"><g fill="none" stroke-linejoin="round" stroke-width="2.7"><path fill="none" stroke="currentColor" d="M36.686 15.171a15.4 15.4 0 0 1 2.529 6.102H44v5.454h-4.785a15.4 15.4 0 0 1-2.529 6.102l3.385 3.385l-3.857 3.857l-3.385-3.385a15.4 15.4 0 0 1-6.102 2.529V44h-5.454v-4.785a15.4 15.4 0 0 1-6.102-2.529l-3.385 3.385l-3.857-3.857l3.385-3.385a15.4 15.4 0 0 1-2.529-6.102H4v-5.454h4.785a15.4 15.4 0 0 1 2.529-6.102l-3.385-3.385l3.857-3.857l3.385 3.385a15.4 15.4 0 0 1 6.102-2.529V4h5.454v4.785a15.4 15.4 0 0 1 6.102 2.529l3.385-3.385l3.857 3.857z"/><path fill="currentColor" stroke="currentColor" d="M24 29a5 5 0 1 0 0-10a5 5 0 0 0 0 10Z"/></g></mask></defs><path fill="currentColor" d="M0 0h48v48H0z" mask="url(#SVGO7QlTCPD)"/></svg>',   // settings icon
        ],
    ]

];
