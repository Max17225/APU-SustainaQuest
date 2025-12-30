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

    <!-- ========== MAIN SECTION ========== -->
    <main class="main-section">
        <?php require $main_section_content; ?>
    </main>

    <!-- Detail Panel (Used when user click on something and system need to display details, but not switch the page) -->
    <!-- script down below  -->
    <div id="detailOverlay" class="detail-overlay">
        <div class="detail-panel">

            <div class="detail-header">
                <h2>Detail Panel</h2>
                <button class="detail-close" id="detailClose">✕</button>
            </div>

            <div class="detail-content" id="detailContent"></div>
        </div>
    </div>

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

<!-- Detail Panel Script -->
<script>
(() => {
    const overlay = document.getElementById('detailOverlay');
    const content = document.getElementById('detailContent');
    const closeBtn = document.getElementById('detailClose');

    const endpoints = {
        quest: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/quest_detail.php',
        submission: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/submission_detail.php',
        user: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/user_detail.php',
        redemption: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/redemption_detail.php'
    };

    document.addEventListener('click', async (e) => {
        const row = e.target.closest('.click-row');
        if (!row) return;

        const type = row.dataset.type;
        const id   = row.dataset.id;

        if (!endpoints[type]) return;

        content.innerHTML = '<p>Loading...</p>';
        overlay.classList.add('active'); // Display overlay panel

        try {
            const res = await fetch(`${endpoints[type]}?id=${id}`); // get the detail content
            content.innerHTML = await res.text(); // inject content
        } catch {
            content.innerHTML = '<p>Error loading detail.</p>';
        }
    });

    closeBtn.onclick = () => overlay.classList.remove('active');
    overlay.onclick = e => {
        if (e.target === overlay) overlay.classList.remove('active');
    };
})();
</script>

<!-- Submission Approval/Rejection Script (Submission detail panel) -->
<script>
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-approve, .btn-reject');
        if (!btn) return;

        const id = btn.dataset.id;
        const isReject = btn.classList.contains('btn-reject');

        let reason = '';

        if (isReject) {
            reason = prompt('Enter declined reason:');
            if (reason === null || reason.trim() === '') {
                alert('Declined reason is required.');
                return;
            }
        }

        const action = isReject ? 'reject' : 'approve';

        const body = new URLSearchParams({
            id,
            reason
        });

        try {
            const res = await fetch(
                `/APU-SustainaQuest/admin/admin_dashboard/process/submission_${action}.php`,
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                }
            );

            const text = await res.text();
            alert(text);
            location.reload();

        } catch (err) {
            alert('Action failed.');
        }
    });
</script>

</html>