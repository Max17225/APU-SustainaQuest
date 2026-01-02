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

<!-- Detail Panel Script (only for admin_dashboard) -->
<script>
    (() => {
        const overlay = document.getElementById('detailOverlay');
        const content = document.getElementById('detailContent');
        const closeBtn = document.getElementById('detailClose');

        const endpoints = {
            quest: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/quest_detail.php',
            submission: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/submission_detail.php',
            user: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/user_detail.php',
            redemption: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/redemption_detail.php',
            quest_delete: '/APU-SustainaQuest/admin/admin_dashboard/detail_panel/delete_detail.php'
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

<!-- Submission Approval/Rejection Script (Submission detail panel)(only for admin_dashboard) -->
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

<!-- Line Chart-->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const data = window.__CHART_DATA__ || {};
        const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        const css = getComputedStyle(document.documentElement);

        const rgb = css.getPropertyValue('--active-op-rgb').trim();
        const lineColor = `rgb(${rgb})`; 

        const gridLineColor = css.getPropertyValue('--non-active').trim();

        Chart.defaults.font.family = '"Aldrich", sans-serif';

        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridLineColor } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridLineColor }
                }
            }
        };

        const createLineChart = (id, dataset) => {
            const canvas = document.getElementById(id);
            if (!canvas || !Array.isArray(dataset)) return;

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        data: dataset,
                        borderColor: lineColor,
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }]
                },
                options
            });
        };

        /* =========================
        Render charts 
        ========================= */
        createLineChart('approveChart',    data.approved);
        createLineChart('rejectChart',     data.rejected);
        createLineChart('redemptionLineChart', data.redemption);

    });
</script>

<!------------------------ Script for (user_management, quest_management, shop_management) ---------------->
<!-- add button -->
<script>
    document.getElementById('addBtn')?.addEventListener('click', () => {
        const params = new URLSearchParams(window.location.search);

        const module = params.get('module');
        const page   = params.get('page');

        window.location.href = `?module=${module}&page=${page}&action=create`;
    });
</script>

<!-- Script for edit button (located inside management table) -->
<script>
    document.addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-btn');
        if (!editBtn) return;

        const params = new URLSearchParams(window.location.search);

        const module = params.get('module');
        const page   = params.get('page');

        const row = editBtn.closest('tr');
        if (!row) return;

        const id = row.dataset.id;
        if (!id) return;

        window.location.href = `?module=${module}&page=${page}&action=edit&id=${id}`;
    });
</script>

<!-- Script for Delete button -->
<script>
    document.getElementById('deleteBtn')?.addEventListener('click', () => {

        const params = new URLSearchParams(window.location.search);
        const module = params.get('module');
        const page   = params.get('page');

        let entity = null;

        if (module === 'user' && page === 'user') entity = 'user';
        if (module === 'user' && page === 'mod')  entity = 'mod';
        if (module === 'quest') entity = 'quest';
        if (module === 'shop')  entity = 'shop';

        if (!entity) {
            alert('Invalid delete target');
            return;
        }

        const ids = [...document.querySelectorAll('.row-check:checked')]
            .map(cb => cb.closest('tr').dataset.id);

        if (!ids.length) {
            alert('Select at least one row.');
            return;
        }

        if (!confirm(`Delete ${ids.length} selected record(s)?`)) return;

        fetch('process/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ entity, ids })
        })
        .then(res => res.text())
        .then(text => {
            try {
                const json = JSON.parse(text);
                if (json.success) location.reload();
            } catch (e) {
                console.error('Server returned:', text);
                alert('Delete failed. Check console.');
            }
        });
    });
</script>

<!-- Script for select All btn -->
<script>
    document.getElementById('selectAll')?.addEventListener('change', e => {
        document.querySelectorAll('.row-check')
            .forEach(cb => cb.checked = e.target.checked);
    });
</script>

<!-- Script for management's top bar (search / sort tools) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const tableBody = document.querySelector('.management-table .record-table tbody');
        if (!tableBody) return;

        let currentSort = null;
        let currentBan  = '0'; // default Normal

        const rows = Array.from(tableBody.querySelectorAll('tr'));

        // get current module
        const currentModule = "<?= $_GET['module'] ?? '' ?>";

        /* =========================
        Search 
        ========================= */
        document.getElementById('searchInput')?.addEventListener('input', e => {
            const keyword = e.target.value.toLowerCase();

            rows.forEach(row => {
                let match = false;

                if (currentModule === 'user') {
                    const name = row.dataset.username || ''; // check for username, display the row which has the similar username
                    match = name.includes(keyword);
                }

                if (currentModule === 'quest') { // check for the row on title and creator
                    const title = row.dataset.title || '';
                    const creator = row.dataset.creator || '';
                    match = title.includes(keyword) || creator.includes(keyword);
                }

                row.style.display = match ? '' : 'none';
            });
        });

        /* =========================
            Filter 
        ========================= */
        document.querySelectorAll('.fil-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.fil-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (currentModule === 'user') {
                    currentBan = btn.dataset.ban; // get the ban data 0 ro 1
                }

                applyFilterAndSort();
            });
        });

        /* =========================
        Sort Buttons
        ========================= */
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {

                if (currentSort === btn.dataset.sort) {
                    currentSort = null;
                    btn.classList.remove('active');
                } else {
                    document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentSort = btn.dataset.sort;
                }

                applyFilterAndSort();
            });
        });

        /* =========================
        Core logic (For sort button)
        ========================= */
        function applyFilterAndSort() {

            let filtered = [...rows];

            // module user and page user (Ban filter)
            if (currentModule === 'user') {
                <?php if (($_GET['page']) === 'user'): ?>
                    filtered = filtered.filter(row =>
                        row.dataset.banned === currentBan
                    );
                <?php endif; ?>
            }

            if (currentSort) {
                filtered.sort((a, b) => {
                    switch (currentSort) {
                        case 'level':
                            return b.dataset.level - a.dataset.level;

                        case 'greenPoints':
                            return b.dataset.points - a.dataset.points;

                        case 'sub-approve':
                            return b.dataset.approved - a.dataset.approved;

                        case 'sub-reject':
                            return b.dataset.rejected - a.dataset.rejected;

                        case 'active':
                            return b.dataset.total - a.dataset.total;

                        case 'inactive':
                            return a.dataset.total - b.dataset.total;
                    }
                });
            } else {
                filtered.sort((a, b) =>
                    a.dataset.username.localeCompare(b.dataset.username)
                );
            }

            tableBody.innerHTML = '';
            filtered.forEach(row => tableBody.appendChild(row));
        }

        applyFilterAndSort();
    });
</script>

<!-- Script for form input (if invalid input give hint) -->
<script>
    document.addEventListener('input', async e => {
        const input = e.target;
        const type = input.dataset.validate; // data-validate (username, email...)
        if (!type) return;

        const form = input.closest('form');
        const mode = form.dataset.mode; //  data-mode form mode create / edit
        const hint = input.nextElementSibling; // get hint inside html
        const value = input.value.trim();

        // EDIT MODE: unchanged field -> skip validation
        if (mode === 'edit' &&
            input.dataset.original !== undefined && // if user didnt modify the data
            value === input.dataset.original) {

            input.classList.remove('valid', 'invalid');
            hint.textContent = '';
            checkForm();
            return;
        }

        if (!value) {
            input.classList.remove('valid', 'invalid');
            hint.textContent = '';
            checkForm();
            return;
        }

        if (type === 'password') {
            if (mode === 'edit' && value === '') {
                input.classList.remove('valid', 'invalid');
                hint.textContent = '';
            } else if (value.length < 8) {
                setInvalid(input, hint, 'Minimum 8 characters');
            } else {
                setValid(input, hint);
            }
            checkForm();
            return;
        }

        const entity = document.querySelector('input[name="entity"]').value;
        const id = document.querySelector('input[name="id"]')?.value ?? '';

        const res = await fetch(
            `/APU-SustainaQuest/admin/process/validate.php?type=${type}&entity=${entity}&id=${id}&value=${encodeURIComponent(value)}`
        );

        const data = await res.json();

        data.valid ? setValid(input, hint) : setInvalid(input, hint, data.message);
        checkForm();
    });

    // For the data without data-validate
    document.addEventListener('change', e => {
        const form = e.target.closest('form[data-mode="edit"]');
        if (!form) return;

        checkForm();
    });

    function setValid(input, hint) {
        input.classList.add('valid');
        input.classList.remove('invalid');
        hint.textContent = '';
    }

    function setInvalid(input, hint, msg) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        hint.textContent = msg;
    }

    function checkForm() {
        const form = document.querySelector('form[data-mode]');
        if (!form) return;
        
        const submit = form.querySelector('button[type="submit"]');
        const mode = form.dataset.mode; // edit or create


        let hasInvalid = false;
        let hasChange = false;

        form.querySelectorAll('input').forEach(input => {

            // invalid check
            if (input.classList.contains('invalid')) {
                hasInvalid = true;
            }

            if (mode !== 'edit') return;

            const original = input.dataset.original;

            // PASSWORD: any input enables update
            if (input.type === 'password') {
                if (input.value.trim() !== '') {
                    hasChange = true;
                }
                return;
            }

            // Radio
            if (input.type === 'radio') {
                if (input.checked && original !== undefined && input.value !== original) {
                    hasChange = true;
                }
                return;
            }

            // NORMAL INPUT (text / number / email)
            if (original !== undefined && input.value !== original) {
                hasChange = true;
            }
        });

        if (mode === 'create') {
            const empty = [...form.querySelectorAll('input[required]')]
                .some(i => !i.value.trim());
            submit.disabled = hasInvalid || empty;
            return;
        }

        submit.disabled = !hasChange || hasInvalid;
    }

    document.addEventListener('DOMContentLoaded', checkForm);

</script>


</html>