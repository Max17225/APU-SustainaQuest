<!-- admin/quest_management/main_section/deleted_quest_table.php -->
<?php
$stmt = $conn->query("
    SELECT
        q.questId,
        q.title,
        q.type,
        q.pointReward,
        q.expReward,
        q.createDate,
        q.isActive,

        d.deleteId,
        d.reason,
        d.deleteDate,

        CASE
            WHEN q.createdByAdminId IS NOT NULL THEN 'Admin'
            WHEN q.createdByModeratorId IS NOT NULL THEN mc.modName
            ELSE 'Unknown'
        END AS createdBy,

        CASE
            WHEN d.deletedByAdminId IS NOT NULL THEN 'Admin'
            WHEN d.deletedByModeratorId IS NOT NULL THEN md.modName
            ELSE 'Unknown'
        END AS deletedBy

    FROM QuestDelete d
    JOIN Quests q
        ON q.questId = d.questId

    LEFT JOIN Moderators mc
        ON q.createdByModeratorId = mc.moderatorId

    LEFT JOIN Moderators md
        ON d.deletedByModeratorId = md.moderatorId

    ORDER BY d.deleteId DESC
");
$quests = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<div class="management deleted-quest">
    <!-- TOP available or deleted selector -->
    <div class="top-type-option">
        <a href="?module=quest&page=available"
            class="type-option-btn">
            Available Quest
        </a>

        <a href="?module=quest&page=deleted"
            class="type-option-btn active">
            Deleted Quest
        </a>
    </div>

     <!-- Table Record -->
    <div class="management-table">
        <div class="table-wrapper">
            <div class="record-table">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Title</th>
                            <th>Creator</th>
                            <th>Delete By</th>
                            <th>Create Date</th>
                            <th>Delete Date</th>
                            <th class='reason-col'>Reason</th>
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

                                <td></td>
                                <td><?= htmlspecialchars($q['title']) ?></td>
                                <td><?= htmlspecialchars($q['createdBy']) ?></td>
                                <td><?= $q['deletedBy'] ?></td>
                                <td><?= date('Y-m-d', strtotime($q['createDate'])) ?></td>
                                <td><?= date('Y-m-d', strtotime($q['deleteDate'])) ?></td>
                                <td class='reason-col'><?= htmlspecialchars($q['reason']) ?></td>
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