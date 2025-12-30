<!-- admin/admin_dashboard/main_section/quest_report/index.php -->

<!-- controller for routing the quest type (daily or weekly) -->
<?php
$type = $_GET['type'] ?? 'daily';

$allowed = ['daily', 'weekly'];

if (!in_array($type, $allowed, true)) {
    $type = 'daily';
}


require __DIR__ . "/{$type}.php";