<!-- admin/layout/layout.php -->

<!-- 
required variables (from controller):
$top_bar_content
$main_section_content
-->

<?php
/* =========================
   Layout Bootstrap
   ========================= */
$config = require __DIR__ . '/config.php';

$theme = $_SESSION['admin_theme'] ?? $config['theme']['default'];
?>

<!DOCTYPE html>
<html lang="<?= $config['html']['lang'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['html']['title'] ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Aldrich&family=Press+Start+2P&display=swap" rel="stylesheet">

    <!-- Base CSS Layout and variable (inside admin/CSS/admin.css) -->
    <?php foreach ($config['css']['base'] as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endforeach; ?>
    
    <!-- Theme override -->
    <link rel="stylesheet" href="<?= sprintf($config['css']['theme'], $theme) ?>">
    
</head>

<body>

    <!-- ========== NAV BAR (GLOBAL) ========== -->
    <nav class="nav-bar">
        <!-- Nav LOGO -->
        <div class="nav-logo">
            <h1 class="brand">
                <img src="<?php echo image_path("logo"); ?>" alt="SustainaQuest Logo">
                <span>SustainaQuest</span>
            </h1>
        </div>

        <!-- Nav Option -->
        <div class="nav-option">
            <ul>
                <?php foreach ($config['nav'] as $item): ?>

                    <li class="nav-item <?= $item['module'] ?>">
                        <a href="?module=<?= $item['module'] ?>"
                        class="<?= ($_GET['module'] ?? 'dashboard') === $item['module'] ? 'active' : '' ?>"
                        title="<?= $item['label'] ?>">

                            <span class="nav-icon">
                                <?= $item['svg'] ?>
                            </span>

                            <span class="nav-label">
                                <?= $item['label'] ?>
                            </span>
                        </a>
                    </li>

                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Nav Logout btn -->
        <div class="nav-logout">
            <a href="<?= resolve_location('logout.php') ?>" class="btn-logout">
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- ========== TOP BAR (MODULE-SPECIFIC) ========== -->
    <header class="top-bar">
        <div class="top-bar-inner">

            <!-- Left: tools -->
            <div class="top-bar-tools">
                <?php require $top_bar_content; ?>
            </div>

            <!-- Right: date & time -->
            <div class="top-bar-datetime">
                <div class="top-bar-date" id="topBarDate"></div>
                <div class="top-bar-time" id="topBarTime"></div>
            </div>

        </div>
    </header>

    <!-- ========== MAIN SECTION (MODULE-SPECIFIC) ========== -->
    <main class="main-section">
        <?php require $main_section_content; ?>
    </main>
    
    <!-- ========== Detail Panel (ADMIN DASHBOARD ONLY) ========== -->
    <!-- Detail Panel (Used when user click on something and system need to display details, but not switch the page) -->
    <!-- For now only admin dashboard using this -->
    <div id="detailOverlay" class="detail-overlay">
        <div class="detail-panel">

            <div class="detail-header">
                <h2>Detail Panel</h2>
                <button class="detail-close" id="detailClose">✕</button>
            </div>

            <div class="detail-content" id="detailContent"></div>
        </div>
    </div>

    <!-- ========== JS ========== -->
     <?php
    // Timer Script
    require_once __DIR__ . '/../js/top_bar_timer.php'; 

    // admin_dashboard script
    if (isset($load_dashboard_script) && $load_dashboard_script === true) {
        require_once __DIR__ . '/../js/admin_dashboard.php';
    }

    // admin_management script
    if (isset($load_management_script) && $load_management_script === true) {
        require_once __DIR__ . '/../js/admin_management.php';
    }

    // shop_management script
    if (isset($load_shop_management_script) && $load_shop_management_script === true) {
        require_once __DIR__ . '/../js/shop_management.php';
    }

    // form script (crate form / edit form)
    if (isset($load_form_script) && $load_form_script === true) {
        require_once __DIR__ . '/../js/form.php';
    }

    // shop_management form script (crate form / edit form)
    if (isset($load_shop_form_script) && $load_shop_form_script === true) {
        require_once __DIR__ . '/../js/shop_management_form.php';
    }

    ?>
</body>

</html>