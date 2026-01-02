<!-- admin/quest_management/main_section/quest_table.php -->

<?php

?>

<div class="quest-management available-quest">
    <!-- TOP available or deleted selector -->
    <div class="top-type-option">
        <a href="?module=quest&page=available"
            class="type-option-btn active">
            Available Quest
        </a>

        <a href="?module=quest&page=deleted"
            class="type-option-btn">
            Deleted Quest
        </a>
    </div>

    <!-- Create Delete Option (Use Js to control) -->
    <div class="create-delete-option">
        <button id="addBtn" class="add-btn">
            Add New +
        </button>

        <button id="deleteBtn" class="del-btn">
            Delete -
        </button>
    </div>

</div>