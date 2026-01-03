<!-- admin/quest_management/top_bar/quest_management.php -->

<div class="management-top-bar">

    <!-- Search bar -->
    <input type="text" id="searchInput" placeholder="Search Title or Creator Name">

    <!-- Sort list -->
    <div class="sort-group">
        <span>Sort By:</span>

        <div class="sort-option">
            <button class="sort-btn" data-sort="createDate">Create Date</button>
            <button class="sort-btn" data-sort="exp">EXP</button>
            <button class="sort-btn" data-sort="greenPoint">Green Point</button>
            <button class="sort-btn" data-sort="activated">Activated</button>
        </div>

    </div>

    <!-- Daily Weekly Filter -->
    <div class="filter">
        <button class="fil-btn left active" data-quest-type="daily">Daily</button>
        <button class="fil-btn right" data-quest-type="weekly">Weekly</button>
    </div>

</div>