<!-- admin/admin_dashboard/main_section/moderator_report.php -->

<?php
/* ===============================
   QUEST CREATED (NOT DELETED)
   =============================== */
$createdQuests = $conn->query("
    SELECT
        q.questId,
        q.title,
        q.pointReward,
        q.expReward,
        q.createDate,
        m.modName AS creatorName

    FROM quests q

    INNER JOIN moderators m 
        ON q.createdByModeratorId = m.moderatorId

    WHERE q.createdByModeratorId IS NOT NULL
      AND q.createdByAdminId IS NULL
      AND q.questId NOT IN (
          SELECT questId FROM questDelete
      )
    ORDER BY q.createDate DESC
")->fetch_all(MYSQLI_ASSOC);


/* ===============================
   QUEST DELETED
   =============================== */
$deletedQuests = $conn->query("
    SELECT
        d.deleteId,
        q.title,
        d.deleteDate,

        COALESCE(m.modName, a.adminName) AS deletedBy,

        CASE
            WHEN q.createdByAdminId IS NOT NULL THEN 'Admin'
            WHEN q.createdByModeratorId IS NOT NULL THEN m2.modName
        END AS creator

    FROM questDelete d
    JOIN quests q 
        ON d.questId = q.questId

    LEFT JOIN moderators m 
        ON d.deletedByModeratorId = m.moderatorId
    LEFT JOIN admins a 
        ON d.deletedByAdminId = a.adminId

    LEFT JOIN moderators m2 
        ON q.createdByModeratorId = m2.moderatorId

    ORDER BY d.deleteDate DESC
")->fetch_all(MYSQLI_ASSOC);


/* ===============================
   SUBMISSION APPROVEMENT
   =============================== */
$submissions = $conn->query("
    SELECT
        qs.submissionId,
        q.title,
        qs.approveStatus,
        qs.verifyDate,
        m.modName AS verifiedBy

    FROM questSubmissions qs

    JOIN quests q 
        ON qs.questId = q.questId

    INNER JOIN Moderators m 
        ON qs.verifiedByModeratorId = m.moderatorId

    WHERE qs.approveStatus != 'Pending'
      AND qs.verifiedByModeratorId IS NOT NULL
      AND qs.verifiedByAdminId IS NULL
      AND qs.verifiedByAi = 0
    ORDER BY qs.verifyDate DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<!-------------------------------------------------------------------------------------------- HTML -->
<div class="admin-dashboard mod">

    <div class="report-panel">
        <div class="left-panel">

            <!-- If the quest getting delete, it wont display here -->
            <div class="quest-created card">
                <h3 class="card-title">Quest Created</h3>
                <div class="table-wrapper">
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Creator</th>
                                <th>Point</th>
                                <th>EXP</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($createdQuests as $quest): ?>
                                <tr class="click-row" data-type="quest" data-id="<?= $quest['questId'] ?>">
                                    <td><?= htmlspecialchars($quest['title']) ?></td>
                                    <td><?= htmlspecialchars($quest['creatorName']) ?></td>
                                    <td><?= (int)$quest['pointReward'] ?></td>
                                    <td><?= (int)$quest['expReward'] ?></td>
                                    <td><?= date('Y-m-d', strtotime($quest['createDate'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>

            <div class="quest-deleted card">
                <h3 class="card-title">Quest Deleted</h3>
                <div class="table-wrapper">
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Delete By</th>
                                <th>Creator</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($deletedQuests as $quest): ?>
                                <tr class="click-row" data-type="quest_delete" data-id="<?= $quest['deleteId'] ?>">
                                    <td><?= htmlspecialchars($quest['title']) ?></td>
                                    <td><?= htmlspecialchars($quest['deleteDate']) ?></td>
                                    <td><?= htmlspecialchars($quest['deletedBy']) ?></td>
                                    <td><?= htmlspecialchars($quest['creator']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>

                    </table>
                </div>
            </div>

        </div>


        <div class="right-panel">
            <div class="submission-approvement card">
                <h3 class="card-title"> Submission Approvement </h3>

                <div class="table-wrapper">
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th class="date-column">Date</th>
                                <th class="time-column">Time</th>
                                <th>Verify By</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($submissions as $row): ?>
                                <tr class="click-row" data-type="submission" data-id="<?= $row['submissionId'] ?>">

                                    <td class="title-column"><?= htmlspecialchars($row['title']) ?></td>

                                    </td>
                                        <td class="
                                            <?= $row['approveStatus'] === 'Rejected' ? 'status-rejected' : '' ?>
                                            <?= $row['approveStatus'] === 'Completed' ? 'status-completed' : '' ?>
                                        ">
                                        <?= htmlspecialchars($row['approveStatus']) ?>
                                    </td>

                                    <td class="date-column"><?= date('Y-m-d', strtotime($row['verifyDate'])) ?></td>
                                    <td class="time-column"><?= date('H:i', strtotime($row['verifyDate'])) ?></td>
                                    <td><?= htmlspecialchars($row['verifiedBy']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>
</div>