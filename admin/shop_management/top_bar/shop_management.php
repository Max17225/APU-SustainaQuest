<!-- admin/shop _management/top_bar/shop_management.php -->

<div class="management-top-bar">

    <!-- Search bar -->
    <input type="text" id="searchInput" placeholder="Search Item Name">

    <!-- Sort list -->
    <div class="sort-group">
        <span>Sort By:</span>

        <div class="sort-option">
            <button class="sort-btn" data-sort="total-redeem">Total Redeem</button>
            <button class="sort-btn" data-sort="total-quantity">Total Quantity</button>
            <button class="sort-btn" data-sort="point-cost">Point Cost</button>
            <button class="sort-btn" data-sort="item-available-status">Availabel Status</button>
        </div>

    </div>

    <!-- Daily Weekly Filter -->
    <div class="filter">
        <button class="fil-btn left active" data-type="permanent">Permanent</button>
        <button class="fil-btn right" data-type="limited">Limited</button>
    </div>

</div>