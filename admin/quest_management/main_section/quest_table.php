<!-- admin/quest_management/main_section/quest_table.php -->
<?php
$stmt = $conn->query("
    SELECT
        q.questId,
        q.title,
        q.description,
        q.type,
        q.pointReward,
        q.expReward,
        q.isActive,
        q.createDate,

        CASE
            WHEN q.createdByAdminId IS NOT NULL THEN 'Admin'
            WHEN q.createdByModeratorId IS NOT NULL THEN m.modName
            ELSE 'Unknown'
        END AS createdBy

    FROM quests q

    LEFT JOIN questDelete d
        ON d.questId = q.questId

    LEFT JOIN moderators m
        ON q.createdByModeratorId = m.moderatorId

    WHERE d.questId IS NULL 
    ORDER BY q.createDate DESC
");
$quests = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<div class="management available-quest">
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

     <!-- Table Record -->
    <div class="management-table">
        <div class="table-wrapper">
            <div class="record-table">
                <table>
                    <thead>
                        <tr>
                            <th>All<input type="checkbox" id="selectAll" class="check-all"></th>
                            <th>Title</th>
                            <th>Creator</th>
                            <th>Point Reward</th>
                            <th>EXP Reward</th>
                            <th>Created Date</th>
                            <th>Active Status</th>
                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($quests as $q): ?>

                                <!-- Lower case for search bar -->
                                <tr
                                    data-id="<?= $q['questId'] ?>"
                                    data-title="<?= strtolower($q['title']) ?>"
                                    data-create-date="<?= $q['createDate'] ?>"
                                    data-exp-reward="<?= $q['expReward'] ?>"
                                    data-point-reward="<?= $q['pointReward'] ?>"
                                    data-is-active="<?= $q['isActive'] ?>"
                                    data-quest-type="<?= strtolower($q['type']) ?>"
                                    data-quest-creator="<?= strtolower($q['createdBy']) ?>" 
                                > 

                                <td><input type="checkbox" class="row-check"></td>
                                <td><?= htmlspecialchars($q['title']) ?></td>
                                <td><?= htmlspecialchars($q['createdBy']) ?></td>
                                <td><?= $q['pointReward'] ?></td>
                                <td><?= $q['expReward'] ?></td>
                                <td><?= date('Y-m-d', strtotime($q['createDate'])) ?></td>

                                <td>
                                    <?= $q['isActive'] ? 'Activated' : 'Disabled' ?>
                                </td>

                                <td>
                                    <button class="edit-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m5 16l-1 4l4-1L19.586 7.414a2 2 0 0 0 0-2.828l-.172-.172a2 2 0 0 0-2.828 0zM15 6l3 3m-5 11h8"/></svg>
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>