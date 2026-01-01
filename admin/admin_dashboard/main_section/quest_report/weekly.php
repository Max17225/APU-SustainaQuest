<!-- admin/admin_dashboard/main_section/quest_report/weekly.php-->

<?php $currentType = $_GET['type'] ?? 'daily'; ?>

<?php
// -------------------------------------------------------------------- Pending submission
$sqlPendingCount = "
    SELECT COUNT(*) AS total
    FROM QuestSubmissions qs
    WHERE qs.approveStatus = 'Pending';
";

$stmt = $conn->prepare($sqlPendingCount);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$pendingCount = $row['total'];

// -------------------------------------------------------------------- submission ratio
$sqlRatio = " 
    SELECT q.questId, q.title, COUNT(qs.submissionId) AS total 
    FROM quests q 
    LEFT JOIN questSubmissions qs ON q.questId = qs.questId 
    
    WHERE q.type = 'Weekly' 
    AND q.isActive = 1 
    GROUP BY q.questId; 
"; 

$stmt = $conn->prepare($sqlRatio); 
$stmt->execute(); 
$result = $stmt->get_result(); 
$submissionRatios = $result->fetch_all(MYSQLI_ASSOC);

// ----------------------------------------------------- Bar chart
$totalSubmissions = array_sum(array_column($submissionRatios, 'total'));

// 3 fixed colors
$barColors = ['#ff00007b', '#0055ff82', '#00ff0d75'];

$barData = [];

foreach ($submissionRatios as $index => $row) {
    $barData[] = [
        'title' => $row['title'],
        'total' => $row['total'],
        'color' => $barColors[$index % 3],
    ];
}

// -------------------------------------------------------------------- Weekly quests (Activate quests)
$sqlWeeklyQuest = "
    SELECT 
        q.questId,
        q.title,

        CASE
            WHEN q.createdByAdminId IS NOT NULL THEN 'Admin'
            ELSE m.modName
        END AS creator,

        q.pointReward,
        q.expReward

    FROM quests q
    LEFT JOIN moderators m 
        ON q.createdByModeratorId = m.moderatorId

    WHERE q.type = 'Weekly'
    AND q.isActive = 1
";

$result = $conn->query($sqlWeeklyQuest);
$weeklyQuests = $result->fetch_all(MYSQLI_ASSOC);

// -------------------------------------------------------------------- right panel
$sqlDailySubmissions = "
    SELECT
        qs.submissionId,
        q.title,
        u.username,
        qs.submitDate,
        qs.approveStatus
    FROM questsubmissions qs
    JOIN quests q ON qs.questId = q.questId
    JOIN users u ON qs.submittedByUserId = u.userId
    WHERE q.type = 'Weekly'
    ORDER BY qs.submitDate DESC
";

$stmt = $conn->prepare($sqlDailySubmissions);
$stmt->execute();

$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
?>

<!-------------------------------------------------------------------------------------------- HTML -->
<div class="admin-dashboard quest">
    <!-- TOP daily weekly selector -->
    <div class="daily-weekly-option">
        <a href="?module=dashboard&page=quest&type=daily"
            class="option-btn daily-btn <?= $currentType === 'daily' ? 'active' : '' ?>">
            Daily Quest
        </a>
        
        <a href="?module=dashboard&page=quest&type=weekly"
            class="option-btn weekly-btn <?= $currentType === 'weekly' ? 'active' : '' ?>">
            Weekly Quest
        </a>
    </div>

    <!-- Report section -->
    <div class="report-panel">
        <div class="left-panel">

            <div class="number-card card">
                <div class="card-title">Pending Submission</div>
                <div class="value"><?= $pendingCount ?></div>
            </div>


            <div class="submission-ratio card">
                <div class="card-title">Submission Ratio</div>

                <div class="ratio-content">

                    <div class="bar-chart">
                        <?php foreach ($barData as $row): ?>
                            <div class="bar-row">
                                <span class="bar-label">
                                    <?= htmlspecialchars($row['title']) ?>
                                </span>

                                <div class="bar-track">
                                    <div class="bar-fill"
                                        style="
                                            width: <?= $totalSubmissions > 0
                                                ? ($row['total'] / $totalSubmissions) * 100
                                                : 0 ?>%;
                                            background-color: <?= $row['color'] ?>;
                                        ">
                                    </div>
                                </div>

                                <span class="bar-value">
                                    <?= $row['total'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>


            <div class="available-quest card">
                <div class="card-title">Available Weekly Quest</div>

                <div class="table-wrapper">
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Creator</th>
                                <th>Point</th>
                                <th>EXP</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($weeklyQuests as $quest): ?>
                                <tr class="click-row" data-type="quest" data-id="<?= $quest['questId'] ?>">
                                    <td><?= htmlspecialchars($quest['title']) ?></td>
                                    <td><?= $quest['creator'] ?></td>
                                    <td><?= $quest['pointReward'] ?></td>
                                    <td><?= $quest['expReward'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>


        <div class="right-panel">
            <div class="submission-record card">
                <div class="card-title pc-version-title">Weekly Quests Submission</div>
                <div class="card-title phone-version-title">Weekly Quests Submission</div>
                
                <div class="table-wrapper">
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th>Quest Title</th>
                                <th>Submitted By</th>
                                <th>Submit Date</th>
                                <th>Submit Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $row): ?>
                                <tr class="click-row" data-type="submission" data-id="<?= $row['submissionId'] ?>">
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($row['submitDate'])) ?></td>
                                    <td><?= date('H:i', strtotime($row['submitDate'])) ?></td>

                                    <td class="
                                        <?= $row['approveStatus'] === 'Rejected' ? 'status-rejected' : '' ?>
                                        <?= $row['approveStatus'] === 'Completed' ? 'status-completed' : '' ?>
                                    ">
                                        <?= htmlspecialchars($row['approveStatus']) ?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>
