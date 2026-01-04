<!-- admin/admin_dashboard/detail_panel/user_detail.php -->

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
        u.userId,
        u.userName,
        u.email,
        u.level,
        u.greenPoints,
        u.isBanned

    FROM users u
    WHERE u.userId = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo '<p>User not found.</p>';
    exit;
}

// Fetch submission records for this user
$stmt = $conn->prepare("
    SELECT
        qs.submissionId,
        q.title AS questTitle,
        qs.approveStatus,
        qs.submitDate,
        qs.verifyDate
    FROM questSubmissions qs
    JOIN quests q ON qs.questId = q.questId
    WHERE qs.submittedByUserId = ?
    ORDER BY qs.submitDate DESC
");
$stmt->bind_param('i', $id);
$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!-- User Details -->
<div class="user-detail-section">
    <div class="user-detail">
        <h3>User Name: <?= htmlspecialchars($data['userName']) ?></h3>

        <p>Email: <?= htmlspecialchars($data['email']) ?></p>
        <p>Level: <?= (int)$data['level'] ?></p>
        <p>Green Points: <?= (int)$data['greenPoints'] ?></p>
        <p>Banned: <?= $data['isBanned'] ? 'Yes' : 'No' ?></p>
    </div>

    <hr>

    <!-- Submission Records -->
    <div class="user-submission-record">
        <h4>Submission Records</h4>

        <div class="table-wrapper">
            <table class="record-table">
                <thead>
                    <tr>
                        <th>Quest</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Verified</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="4">No submissions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['questTitle']) ?></td>
                                <td><?= htmlspecialchars($row['approveStatus']) ?></td>
                                <td><?= htmlspecialchars($row['submitDate']) ?></td>
                                <td><?= htmlspecialchars($row['verifyDate'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
