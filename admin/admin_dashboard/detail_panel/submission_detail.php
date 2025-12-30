<!-- admin/admin_dashboard/detail_panel/submission_detail.php -->

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
        qs.submissionId,
        qs.submitDate,
        qs.verifyDate,
        qs.approveStatus,
        qs.declinedReason,
        qs.evidencePictureURL,
        qs.evidenceVideoURL,

        u.userId,
        u.userName,

        q.questId,
        q.title,
        q.questIconURL,
        q.description,

        CASE
            WHEN q.createdByModeratorId IS NOT NULL THEN m_creator.modName
            ELSE 'Admin'
        END AS questCreator,

        CASE
            WHEN qs.verifiedByAi = 1 THEN 'AI'
            WHEN qs.verifiedByAdminId IS NOT NULL THEN 'Admin'
            WHEN qs.verifiedByModeratorId IS NOT NULL THEN m_verifier.modName
            ELSE 'Pending'
        END AS verifiedBy

    FROM QuestSubmissions qs

    INNER JOIN Quests q
        ON qs.questId = q.questId

    INNER JOIN Users u
        ON qs.submittedByUserId = u.userId

    LEFT JOIN Moderators m_creator
        ON q.createdByModeratorId = m_creator.moderatorId

    LEFT JOIN Moderators m_verifier
        ON qs.verifiedByModeratorId = m_verifier.moderatorId

    LEFT JOIN Admins a_verifier
        ON qs.verifiedByAdminId = a_verifier.adminId

    WHERE qs.submissionId = ?;
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
$questIconPath = '/APU-SustainaQuest/' . ltrim($data['questIconURL'], '/');
$evidencePicturePath = '/APU-SustainaQuest/' . ltrim($data['evidencePictureURL'], '/');

if (!empty($data['evidenceVideoURL'])) {
    $evidenceVideoPath = '/APU-SustainaQuest/' . ltrim($data['evidenceVideoURL'], '/');
} else {
    $evidenceVideoPath = null; 
}
?>

<!-- submission Details -->
<div class="submission-detail">
        <!-- Quest info -->
        <div class="quest-info">
            <img src="<?= htmlspecialchars($questIconPath) ?>" alt="Quest Icon" class="quest-icon">
            <h3>Quest Title: <?= htmlspecialchars($data['title']) ?></h3>
            <p>Created By: <?= htmlspecialchars($data['questCreator']) ?></p>
            <p class="description"><?= nl2br(htmlspecialchars($data['description'])) ?></p> 
        </div>

        <!-- Submission info -->
        <div class="submission-info">
            <h3>Submission Details</h3>
            <img src="<?= htmlspecialchars($evidencePicturePath) ?>" alt="Evidence Picture" class="evidence-picture">

            <?php if ($evidenceVideoPath): ?>
                <video controls class="evidence-video">
                    <source src="<?= htmlspecialchars($evidenceVideoPath) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>

            <p>Submitted By: <?= htmlspecialchars($data['userName']) ?> (User ID: <?= htmlspecialchars($data['userId']) ?>)</p>
            <p>Submit Date: <?= htmlspecialchars($data['submitDate']) ?></p>
            <p>Approval Status: <?= htmlspecialchars($data['approveStatus']) ?></p>

            <?php if ($data['approveStatus'] === 'Rejected'): ?>
                <p class="declined-reason">Declined Reason: <?= nl2br(htmlspecialchars($data['declinedReason'])) ?></p>
            <?php endif; ?>

            <!-- If quest submission was not pending -->
            <?php if ($data['approveStatus'] !== 'Pending'): ?>
                <p>Verified By: <?= htmlspecialchars($data['verifiedBy']) ?></p>
                <p>Verify Date: <?= htmlspecialchars($data['verifyDate']) ?></p>
            <?php endif; ?>

            <!-- Admin can approve or reject if still pending -->
            <?php if ($data['approveStatus'] === 'Pending'): ?>
                <div class="detail-action">
                    <button class="btn-approve" data-id="<?= $data['submissionId'] ?>">
                        Approve
                    </button>

                    <button class="btn-reject" data-id="<?= $data['submissionId'] ?>">
                        Reject
                    </button>
                </div>
            <?php endif; ?>

        </div>
</div>
