<!-- admin/admin_dashboard/main_section/quest_report/daily.php-->

<?php $currentType = $_GET['type'] ?? 'daily';?>


<?php
// -------------------------------------------------------------------- Today submission
$sqlTodayCount = "
    SELECT COUNT(*) AS total
    FROM questsubmissions qs
    JOIN quests q ON qs.questId = q.questId
    WHERE q.type = 'Daily'
      AND q.isActive = 1
";

$stmt = $conn->prepare($sqlTodayCount);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$todaySubmission = $row['total'];

// -------------------------------------------------------------------- submission ratio
$sqlRatio = "
    SELECT q.questId, q.title, COUNT(qs.submissionId) AS total
    FROM quests q
    LEFT JOIN questSubmissions qs
        ON q.questId = qs.questId
    WHERE q.type = 'Daily'
      AND q.isActive = 1
    GROUP BY q.questId
";

$stmt = $conn->prepare($sqlRatio);
$stmt->execute();

$result = $stmt->get_result();
$submissionRatios = $result->fetch_all(MYSQLI_ASSOC);

// ----------------------------------------------------- PIE chart
$totalSubmissions = array_sum(array_column($submissionRatios, 'total'));

$gradientParts = [];
$currentAngle = 0;

// fallback color palette
$colors = ['#ff00007b', '#eeff007d', '#0055ff82', '#00ff0d75', '#ae00ff64'];
$currentAngle = 0;

foreach ($submissionRatios as $index => $row) {
    // color is fixed by quest order
    $color = $colors[$index % count($colors)];

    if ($row['total'] <= 0) {
        // skip slice, but color order stays intact
        continue;
    }

    $percentage = $row['total'] / $totalSubmissions;
    $angle = round($percentage * 360);

    $start = $currentAngle;
    $end   = $currentAngle + $angle;

    $gradientParts[] = "{$color} {$start}deg {$end}deg";

    $currentAngle = $end;
}

// If no submission (prevent error, if some quest is not even have one submission)
$pieGradient = $gradientParts
    ? implode(', ', $gradientParts)
    : '#ccc 0deg 360deg';

// -------------------------------------------------------------------- today quest
$sqlTodayQuest = "
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

    WHERE q.type = 'Daily'
    AND q.isActive = 1
";

$result = $conn->query($sqlTodayQuest);
$todayQuests = $result->fetch_all(MYSQLI_ASSOC);

// -------------------------------------------------------------------- right panel
$sqlDailySubmissions = "
    SELECT
        qs.submissionId,
        q.title,
        u.username,
        qs.submitDate
    FROM questsubmissions qs
    JOIN quests q ON qs.questId = q.questId
    JOIN users u ON qs.submittedByUserId = u.userId
    WHERE q.type = 'Daily'
      AND qs.verifiedByAi = 1
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
    <div class="top-type-option">
        <a href="?module=dashboard&page=quest&type=daily"
            class="type-option-btn <?= $currentType === 'daily' ? 'active' : '' ?>">
            Daily Quest
        </a>

        <a href="?module=dashboard&page=quest&type=weekly"
            class="type-option-btn <?= $currentType === 'weekly' ? 'active' : '' ?>">
            Weekly Quest
        </a>
    </div>

    <!-- Report section -->
    <div class="report-panel">
        <div class="left-panel">

            <div class="number-card card">
                <h3 class="card-title">Today Submission</h3>
                <div class="value"><?= $todaySubmission ?></div>
            </div>


            <div class="submission-ratio card">
                <h3 class="card-title">Submission Ratio</h3>

                <div class="ratio-content">
                    <div class="pie-chart"
                        style="background: conic-gradient(<?= $pieGradient ?>);">
                    </div>

                    <ul class="ratio-list">
                        <?php foreach ($submissionRatios as $index => $row): ?>
                            <li style="--quest-color: <?= $colors[$index] ?>;">
                                <span class="color-dot"></span>
                                <?= htmlspecialchars($row['title']) ?>
                                <span> (<?= $row['total'] ?>) </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>


            <div class="available-quest card">
                <h3 class="card-title">Today's Quest</h3>

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
                            <?php foreach ($todayQuests as $quest): ?>
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
                <h3 class="card-title pc-version-title">Daily Quests Submission (Verify By AI)</h3>
                <h3 class="card-title phone-version-title">Daily Quests Submission</h3>

                <div class="table-wrapper">
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th>Quest Title</th>
                                <th>Submitted By</th>
                                <th>Submit Date</th>
                                <th>Submit Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $row): ?>
                                <tr class="click-row" data-type="submission" data-id="<?= $row['submissionId'] ?>">
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($row['submitDate'])) ?></td>
                                    <td><?= date('H:i', strtotime($row['submitDate'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>