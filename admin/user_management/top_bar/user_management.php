<!-- admin/user_management/top_bar/user_management.php -->

<?php
$page = $_GET['page'] ?? 'user'; // user | mod
$isUser = ($page === 'user'); // user and mod has difference top bar display
?>

<div class="management-top-bar">

    <!-- Search bar -->
    <input type="text" id="searchInput" placeholder="Search Username">

    <!-- Sort list (user only)-->
    <?php if ($isUser): ?>
    <div class="sort-group">
        <span>Sort By:</span>

        <div class="sort-option">
            <button class="sort-btn" data-sort="level">Level</button>
            <button class="sort-btn" data-sort="greenPoints">Green Point</button>
            <button class="sort-btn" data-sort="sub-approve">Sub Approve</button>
            <button class="sort-btn" data-sort="sub-reject">Sub Reject</button>
            <button class="sort-btn" data-sort="active">Active</button>
            <button class="sort-btn" data-sort="inactive">Inactive</button>
        </div>

    </div>
    <?php endif; ?>

    <!-- Ban Filter (user only) -->
    <?php if ($isUser): ?>
    <div class="filter">
        <button class="fil-btn left active" data-ban="0">Normal</button>
        <button class="fil-btn right" data-ban="1">Banned</button>
    </div>
    <?php endif; ?>

</div>