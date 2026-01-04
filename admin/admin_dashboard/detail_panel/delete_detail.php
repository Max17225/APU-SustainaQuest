<!-- admin/admin_dashboard/detail_panel/delete_detail.php -->

<?php
// This file is loaded via AJAX(Not connect with global scope) to show details in the detail panel.
// Need to require necessary files for DB connection and session check. 
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/session_check.php';
session_start();
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

/* ===============================
   Fetch deleted quest + details
   =============================== */
$stmt = $conn->prepare("
    SELECT
        q.questId,
        q.title,
        q.description,
        q.questIconURL,
        q.pointReward,
        q.expReward,
        q.createDate,

        d.reason,
        d.deleteDate,

        CASE
            WHEN d.deletedByAdminId IS NOT NULL THEN 'Admin'
            ELSE m.modName
        END AS deletedBy

    FROM questDelete d
    JOIN quests q ON d.questId = q.questId
    LEFT JOIN Moderators m ON d.deletedByModeratorId = m.moderatorId
    WHERE d.deleteId = ?
");

$stmt->bind_param('i', $id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo '<p>Deleted quest not found.</p>';
    exit;
}

/* ===============================
   Icon path handling
   =============================== */
$iconPath = '/APU-SustainaQuest/' . ltrim($data['questIconURL'], '/');
?>

<div class="quest-detail deleted">

    <img src="<?= htmlspecialchars($iconPath) ?>" alt="Quest Icon" class="quest-icon">

    <h3>Quest Title: <?= htmlspecialchars($data['title']) ?></h3>

    <p class="description">
        <?= nl2br(htmlspecialchars($data['description'])) ?>
    </p>

    <p>Created On: <?= htmlspecialchars($data['createDate']) ?></p>
    <p>Point Reward: <?= (int)$data['pointReward'] ?></p>
    <p>EXP Reward: <?= (int)$data['expReward'] ?></p>

    <hr>

    <p>Deleted By: <?= htmlspecialchars($data['deletedBy']) ?></p>
    <p>Deleted On: <?= htmlspecialchars($data['deleteDate']) ?></p>

    <p class="delete-reason-title">Delete Reason:</p>
    <p class="delete-reason">
        <?= nl2br(htmlspecialchars($data['reason'])) ?>
    </p>

</div>