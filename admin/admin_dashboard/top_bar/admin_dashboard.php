<!-- admin/admin_dashboard/top_bar/admin_dashboard.php -->

<?php $currentPage = $_GET['page'] ?? 'quest'; ?>

<div class="admin-dashboard-top-bar">
    <span class="tool-title">Information Overview</span>

    <button class="left-btn" aria-label="Previous">‹</button>

    <div class="tool-option">
        <a href="?module=dashboard&page=quest"
            class="tool-btn <?= $currentPage === 'quest' ? 'active' : '' ?>">
            Quests
        </a>

        <a href="?module=dashboard&page=user"
            class="tool-btn <?= $currentPage === 'user' ? 'active' : '' ?>">
            Users
        </a>

        <a href="?module=dashboard&page=mod"
            class="tool-btn <?= $currentPage === 'mod' ? 'active' : '' ?>">
            Moderators
        </a>

        <a href="?module=dashboard&page=shop"
            class="tool-btn <?= $currentPage === 'shop' ? 'active' : '' ?>">
            Shop
        </a>
    </div>

    <button class="right-btn" aria-label="Next">›</button>
</div>

<!-- switch page button script -->
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

