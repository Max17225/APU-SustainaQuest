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

