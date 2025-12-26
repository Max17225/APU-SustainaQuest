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
    <title><?= $config['html']['title'] ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Aldrich&family=Press+Start+2P&display=swap" rel="stylesheet">

    <!-- Base CSS Layout and variable (inside admin/CSS/admin.css) -->
    <?php foreach ($config['css']['base'] as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endforeach; ?>
    
    <!-- Theme -->
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
                    <li>
                        <a href="?module=<?= $item['module'] ?>" class="<?= ($_GET['module'] ?? 'dashboard') === $item['module'] ? 'active' : '' ?>">
                            <?= $item['label'] ?>
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

    <!-- ========== MAIN SECTION ========== -->
    <main class="main-section">
        <?php require $main_section_content; ?>
    </main>

</body>

<!-- Timer script -->
<script>
    function updateTopBarTime() {
        const now = new Date();

        document.getElementById('topBarDate').textContent =
            now.toLocaleDateString(undefined, {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

        document.getElementById('topBarTime').textContent =
            now.toLocaleTimeString(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
    }

    // initial render
    updateTopBarTime();

    // update every second
    setInterval(updateTopBarTime, 1000);
</script>


</html>