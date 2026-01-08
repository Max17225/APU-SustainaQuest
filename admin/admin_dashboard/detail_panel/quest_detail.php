<!-- admin/admin_dashboard/detail_panel/quest_detail.php -->

<?php
// This file is loaded via AJAX(Not connect with global scope) to show details in the detail panel.
// Need to require necessary files for DB connection and session check. 
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/session_check.php';
session_start();
require_role('admin');


$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT 
        q.questId,
        q.title,

        CASE
            WHEN q.createdByAdminId IS NOT NULL THEN 'Admin'
            ELSE m.modName
        END AS creator,

        q.pointReward,
        q.expReward,
        q.questIconURL,
        q.description,
        q.createDate

    FROM quests q
    LEFT JOIN moderators m 
        ON q.createdByModeratorId = m.moderatorId
    WHERE questId = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo '<p>Quest not found.</p>';
    exit;
}
?>

<?php
if (isset($data['questIconURL'])) {
    $iconPath = '/APU-SustainaQuest/' . ltrim($data['questIconURL'], '/');
}
?>

<!-- Quest Details -->
<div class="quest-detail">
    <img src="<?= htmlspecialchars($iconPath ?? '' ) ?>" alt="Quest Icon" class="quest-icon">

    <h3>Quest Title: <?= htmlspecialchars($data['title']) ?></h3>

    <p class="description"><?= nl2br(htmlspecialchars($data['description'])) ?></p>

    <p>Created By: <?= htmlspecialchars($data['creator']) ?></p>
    <p>Created On: <?= htmlspecialchars($data['createDate']) ?></p>
    <p>Point Reward: <?= htmlspecialchars($data['pointReward']) ?></p>
    <p>EXP Reward: <?= htmlspecialchars($data['expReward']) ?></p>
    
</div>