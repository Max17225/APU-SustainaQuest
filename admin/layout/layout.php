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

<!-- switch page button script (admin_dashboard) -->
<script>
    (function () {
        const bar = document.querySelector('.admin-dashboard-top-bar');
        if (!bar) return;

        const leftBtn  = bar.querySelector('.left-btn');
        const rightBtn = bar.querySelector('.right-btn');

        if (!leftBtn || !rightBtn) return;

        // Order definition (single source of truth)
        const pages = ['quest', 'user', 'mod', 'shop'];

        // Read current page from URL
        const params = new URLSearchParams(window.location.search);
        const module = params.get('module') || 'dashboard';
        const current = params.get('page') || 'quest';

        let index = pages.indexOf(current);
        if (index === -1) index = 0;

        function goTo(i) {
            const page = pages[i];
            window.location.href = `?module=${module}&page=${page}`;
        }

        leftBtn.addEventListener('click', () => {
            index = (index - 1 + pages.length) % pages.length;
            goTo(index);
        });

        rightBtn.addEventListener('click', () => {
            index = (index + 1) % pages.length;
            goTo(index);
        });
    })();
</script>

<!------------------------ Script for (user_management, quest_management, shop_management) ---------------->
<script>
    document.querySelectorAll('.fil-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;

            document.querySelector('.status-permanent').style.display =
                type === 'permanent' ? 'inline' : 'none';

            document.querySelector('.status-limited').style.display =
                type === 'limited' ? 'inline' : 'none';
        });
    });
</script>


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

        // quest delete need reason
        let reason = null;
        if (entity === 'quest') {
            reason = prompt('Enter delete reason for selected quest(s):');
            if (!reason || !reason.trim()) {
                alert('Delete reason is required.');
                return;
            }
        } else {
            if (!confirm(`Delete ${ids.length} selected record(s)?`)) return;
        }


        fetch('process/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ entity, ids, reason })
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

<!-- Script for management's top bar (search / sort tools / filter) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const tableBody = document.querySelector('.management-table .record-table tbody');
        if (!tableBody) return;

        const rows = Array.from(tableBody.querySelectorAll('tr'));

        let currentSort = null;

        /* =========================
        Current states
        ========================= */
        let currentBan       = '0';          // user
        let currentQuestType = 'daily';      // quest
        let currentItemType  = 'permanent';  // shop

        const currentModule = "<?= $_GET['module'] ?? '' ?>";
        const currentPage   = "<?= $_GET['page'] ?? '' ?>";

        /* =========================
        SEARCH
        ========================= */
        document.getElementById('searchInput')?.addEventListener('input', e => {
            const keyword = e.target.value.toLowerCase();

            rows.forEach(row => {
                let match = false;

                if (currentModule === 'user') {
                    match = (row.dataset.username || '').includes(keyword);
                }

                if (currentModule === 'quest') {
                    match =
                        (row.dataset.title || '').includes(keyword) ||
                        (row.dataset.questCreator || '').includes(keyword);
                }

                if (currentModule === 'shop') {
                    match = (row.dataset.name || '').includes(keyword);
                }

                row.style.display = match ? '' : 'none';
            });
        });

        /* =========================
        FILTER BUTTONS
        ========================= */
        document.querySelectorAll('.fil-btn').forEach(btn => {
            btn.addEventListener('click', () => {

                document.querySelectorAll('.fil-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (currentModule === 'user') { // ban or normal
                    currentBan = btn.dataset.ban;
                }

                if (currentModule === 'quest') { // daily or weekly
                    currentQuestType = btn.dataset.questType.toLowerCase();
                }

                if (currentModule === 'shop') {
                    currentItemType = btn.dataset.type.toLowerCase(); 
                    // permanent | limited
                }

                applyFilterAndSort();
            });
        });

        /* =========================
        SORT BUTTONS
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
        CORE LOGIC
        ========================= */
        function applyFilterAndSort() {

            let filtered = [...rows];

            /* ---------- FILTER ---------- */
            if (currentModule === 'user' && currentPage === 'user') {
                filtered = filtered.filter(row => row.dataset.banned === currentBan);
            }

            if (currentModule === 'quest') {
                filtered = filtered.filter(row => row.dataset.questType === currentQuestType);
            }

            if (currentModule === 'shop') {
                filtered = filtered.filter(row => row.dataset.type === currentItemType);
            }

            /* ---------- SORT ---------- */
            if (currentSort) {
                filtered.sort((a, b) => {

                    /* USER */
                    if (currentModule === 'user') {
                        switch (currentSort) {
                            case 'level':        return b.dataset.level - a.dataset.level;
                            case 'greenPoints':  return b.dataset.points - a.dataset.points;
                            case 'sub-approve':  return b.dataset.approved - a.dataset.approved;
                            case 'sub-reject':   return b.dataset.rejected - a.dataset.rejected;
                            case 'active':       return b.dataset.total - a.dataset.total;
                            case 'inactive':     return a.dataset.total - b.dataset.total;
                        }
                    }

                    /* QUEST */
                    if (currentModule === 'quest') {
                        switch (currentSort) {
                            case 'createDate':
                                return new Date(b.dataset.createDate) - new Date(a.dataset.createDate);
                            case 'exp':
                                return b.dataset.expReward - a.dataset.expReward;
                            case 'greenPoint':
                                return b.dataset.pointReward - a.dataset.pointReward;
                            case 'activated':
                                return b.dataset.isActive - a.dataset.isActive;
                        }
                    }

                    /* SHOP */
                    if (currentModule === 'shop') {
                        switch (currentSort) {
                            case 'total-redeem':
                                return b.dataset.total - a.dataset.total;
                            case 'total-quantity':
                                return b.dataset.quantity - a.dataset.quantity;
                            case 'point-cost':
                                return b.dataset.point - a.dataset.point;
                            case 'item-available-status':
                                return b.dataset.status - a.dataset.status;
                        }
                    }

                    return 0;
                });
            } else {
                /* DEFAULT SORT */
                if (currentModule === 'user') {
                    filtered.sort((a, b) =>
                        a.dataset.username.localeCompare(b.dataset.username)
                    );
                }

                if (currentModule === 'quest') {
                    filtered.sort((a, b) =>
                        a.dataset.title.localeCompare(b.dataset.title)
                    );
                }

                if (currentModule === 'shop') {
                    filtered.sort((a, b) =>
                        a.dataset.name.localeCompare(b.dataset.name)
                    );
                }
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
        const hint = input.nextElementSibling; 
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

            //Check for Edit, check if they change any data or not, if yes enable update button to click
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

            // file
            if (input.type === 'file') {
                if (input.files && input.files.length > 0) {
                    hasChange = true;
                }
                return;
            }
        });

        /* =========================
            TEXTAREA (Check if they edit text area)
           ========================= */
        form.querySelectorAll('textarea').forEach(textarea => {
            if (mode !== 'edit') return;

            const original = textarea.dataset.original ?? '';

            if (textarea.value !== original) {
                hasChange = true;
            }
        });


        if (mode === 'create') {
            let empty = false;

            // Required inputs (text, number, email)
            form.querySelectorAll('input[required]:not([type="radio"]):not([type="file"])')
                .forEach(i => {
                    if (!i.value.trim()) empty = true;
                });

            // Required textarea
            form.querySelectorAll('textarea[required]')
                .forEach(t => {
                    if (!t.value.trim()) empty = true;
                });

            // Required file
            form.querySelectorAll('input[type="file"][required]')
                .forEach(f => {
                    if (!f.files || f.files.length === 0) empty = true;
                });

            // Required radio groups
            const radioNames = new Set(
                [...form.querySelectorAll('input[type="radio"][required]')].map(r => r.name)
            );

            radioNames.forEach(name => {
                if (!form.querySelector(`input[type="radio"][name="${name}"]:checked`)) {
                    empty = true;
                }
            });

            submit.disabled = hasInvalid || empty;
            return;
        }

        // for edit mode
        submit.disabled = !hasChange || hasInvalid;
    }

    document.addEventListener('DOMContentLoaded', checkForm);
    document.addEventListener('input', checkForm);
    document.addEventListener('change', checkForm);

</script>

<!-- Click Image to change -->
<script>
    const fileInput = document.getElementById('icon');
    const frame = document.getElementById('imageFrame');
    const preview = document.getElementById('previewImg');

    frame.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.hidden = false;

            const placeholder = frame.querySelector('.placeholder');
            if (placeholder) placeholder.remove();
        };
        reader.readAsDataURL(file);
    });
</script>

<!-- Script for shop management from only (Create)-->
<script>
    document.addEventListener('DOMContentLoaded', async () => {

        const form = document.querySelector('form[data-mode="create"]');
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        const hint = document.getElementById('itemTypeHint');
        const radios = form.querySelectorAll('input[name="itemType"]');

        let permanentCount = 0;

        // fetch total count
        try {
            const res = await fetch(
                '/APU-SustainaQuest/admin/shop_management/process/get_item_count.php?type=Permanent'
            );
            const data = await res.json();
            permanentCount = Number(data.count) || 0;
        } catch {
            permanentCount = 0;
        }

        // type selection logic
        radios.forEach(radio => {
            radio.addEventListener('change', () => {

                // Reset state
                hint.textContent = '';
                submitBtn.disabled = false;

                if (radio.value === 'Permanent' && permanentCount >= 8) {
                    hint.textContent = 'Maximum 8 Permanent items allowed.';
                    submitBtn.disabled = true;

                    // Auto-uncheck
                    radio.checked = false;
                }
            });
        });

    });
</script>

<!-- Script for shop management from only (Edit) -->
<script>
    document.addEventListener('change', async e => {

        const input = e.target;
        if (input.name !== 'availableStatus' || input.value !== '1') return;

        const hint = document.getElementById('limitHint');
        const submit = document.querySelector('button[type="submit"]');

        const res = await fetch('/APU-SustainaQuest/admin/shop_management/process/check_limited_available.php');
        const data = await res.json();

        if (!data.allowed) {
            hint.textContent = 'Maximum 8 limited items can be available.';
            submit.disabled = true;
            input.checked = false;
        } else {
            hint.textContent = '';
        }
    });
</script>

</html>