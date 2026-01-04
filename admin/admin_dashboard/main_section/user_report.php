<!-- admin/admin_dashboard/main_section/user_report.php -->

<?php
// -------------------------------------------------------------- top panel
// Fetch total users
$stmt = $conn->query("
    SELECT COUNT(*) AS total 
    FROM Users
");
$totalUsers = (int) $stmt->fetch_assoc()['total'];

// Fetch banned users
$stmt = $conn->query("
    SELECT COUNT(*) AS total 
    FROM Users
    WHERE isBanned = 1
");
$bannedUsers = (int) $stmt->fetch_assoc()['total'];

// Fetch average green points
$stmt = $conn->query("
    SELECT AVG(greenPoints) AS average 
    FROM Users
");
$averageGreenPoints = (float) $stmt->fetch_assoc()['average'];

// -------------------------------------------------------------- bottom panel
$year = date('Y');

// Fetch approved submissions per month
$approvedData = array_fill(1, 12, 0);

$stmt = $conn->prepare("
    SELECT 
        MONTH(verifyDate) AS month,
        COUNT(*) AS total
    FROM QuestSubmissions
    WHERE approveStatus = 'Completed'
      AND YEAR(verifyDate) = ?
    GROUP BY MONTH(verifyDate)
");
$stmt->bind_param('i', $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $approvedData[(int)$row['month']] = (int)$row['total'];
}

// Fetch rejected submissions per month
$rejectedData = array_fill(1, 12, 0);

$stmt = $conn->prepare("
    SELECT 
        MONTH(verifyDate) AS month,
        COUNT(*) AS total
    FROM QuestSubmissions
    WHERE approveStatus = 'Rejected'
      AND YEAR(verifyDate) = ?
    GROUP BY MONTH(verifyDate)
");
$stmt->bind_param('i', $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $rejectedData[(int)$row['month']] = (int)$row['total'];
}

// Fetch leaderboard data
$stmt = $conn->query("
    SELECT 
        u.userId,
        u.userName,
        u.level,
        u.greenPoints
    FROM Users u
    ORDER BY u.greenPoints DESC
    LIMIT 10
");

$leaderboard = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<div class="admin-dashboard user">

    <div class="top-panel">

        <!-- Total User Card -->
        <div class="user-card number-card card">
            <div class="card-title">Total User</div>
            <div class="value"><?= $totalUsers ?></div>
        </div>

        <!-- Banned User Card -->
        <div class="user-card number-card card">
            <div class="card-title">Banned User</div>
            <div class="value"><?= $bannedUsers ?></div>
        </div>

        <!-- Average green point -->
        <div class="user-card number-card card">
            <div class="card-title">Average Green Point</div>
            <div class="value"><?= $averageGreenPoints ?></div>
        </div>

    </div>

    <div class="bottom-panel">

        <!-- User Activity -->
        <div class="user-activity-graph card">
            <h3 class="card-title">
                User Activity <?= $year ?>
            </h3>

            <div class="submission-approve">
                <h4>Submission Approved</h4>
                <canvas id="approveChart"></canvas>
            </div>

            <div class="submission-reject">
                <h4>Submission Rejected</h4>
                <canvas id="rejectChart"></canvas>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="leaderboard card">
            <h3 class="card-title">Leaderboard (Green Points)</h3>

            <div class="table-wrapper">
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Lv</th>
                            <th>Green Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaderboard as $row): ?>
                            <tr class="click-row" data-type="user" data-id="<?= $row['userId'] ?>">
                                <td><?= htmlspecialchars($row['userName']) ?></td>
                                <td><?= (int)$row['level'] ?></td>
                                <td><?= (int)$row['greenPoints'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

<script>
window.__CHART_DATA__ = window.__CHART_DATA__ || {};
window.__CHART_DATA__.approved = <?= json_encode(array_values($approvedData)) ?>;
window.__CHART_DATA__.rejected = <?= json_encode(array_values($rejectedData)) ?>;
</script>