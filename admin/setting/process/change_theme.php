<!-- admin/setting/process/change_theme.php -->

<?php
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/session_check.php';

session_start();
require_role('admin');


$theme = $_POST['theme'] ?? 'Dark';

$_SESSION['admin_theme'] = $theme;

header('Location: /APU-SustainaQuest/admin/?module=setting');
exit;