<!-- admin/js/admin_dashboard.php-->

<script>
    // Detail Panel Script
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

<script>
    // Submission Approval/Rejection Script (Submission detail panel)(only for admin_dashboard)
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Line Chart
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

<script>
    // switch page button script (admin_dashboard)
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
