<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only moderators allowed
require_role('moderator');

// Include header
$path = '../';
require_once '../includes/header.php';

// -----------------------------
// KPI queries (Weekly logic)
// -----------------------------

// Pending weekly submissions
$sql_pending = "
  SELECT COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE qs.approveStatus = 'Pending'
    AND q.type = 'Weekly'
";
$pending_count = (int)($conn->query($sql_pending)->fetch_assoc()['total'] ?? 0);

// Approved today
$sql_approved_today = "
  SELECT COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE qs.approveStatus = 'Approved'
    AND q.type = 'Weekly'
    AND DATE(qs.verifyDate) = CURDATE()
";
$approved_today = (int)($conn->query($sql_approved_today)->fetch_assoc()['total'] ?? 0);

// Rejected today
$sql_rejected_today = "
  SELECT COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE qs.approveStatus = 'Rejected'
    AND q.type = 'Weekly'
    AND DATE(qs.verifyDate) = CURDATE()
";
$rejected_today = (int)($conn->query($sql_rejected_today)->fetch_assoc()['total'] ?? 0);

// Active weekly quests
$sql_active_quests = "
  SELECT COUNT(*) AS total
  FROM quests
  WHERE type = 'Weekly' AND isActive = 1
";
$active_quests = (int)($conn->query($sql_active_quests)->fetch_assoc()['total'] ?? 0);

// Total users
$sql_total_users = "SELECT COUNT(*) AS total FROM users";
$total_users = (int)($conn->query($sql_total_users)->fetch_assoc()['total'] ?? 0);

// Approval rate
$sql_approval_rate = "
  SELECT
    SUM(CASE WHEN qs.approveStatus='Approved' THEN 1 ELSE 0 END) AS approved,
    COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE q.type = 'Weekly'
";
$row = $conn->query($sql_approval_rate)->fetch_assoc();
$approval_rate = ($row['total'] > 0) ? round(($row['approved'] / $row['total']) * 100) : 0;
?>

<style>
.dashboard-wrapper { max-width:1200px; margin:20px auto; padding:20px; }
.kpi_row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
.kpi { background:#fff; border-radius:16px; padding:16px; box-shadow:0 10px 24px rgba(0,0,0,.08); }
.title { font-size:13px; color:#64748b; font-weight:700; }
.num { font-size:28px; font-weight:900; }
</style>

<div class="dashboard-wrapper">

<h1>Moderator Dashboard</h1>

<div class="kpi_row">
  <div class="kpi"><div class="title">Pending</div><div class="num"><?= $pending_count ?></div></div>
  <div class="kpi"><div class="title">Approved Today</div><div class="num"><?= $approved_today ?></div></div>
  <div class="kpi"><div class="title">Rejected Today</div><div class="num"><?= $rejected_today ?></div></div>
  <div class="kpi"><div class="title">Active Weekly Quests</div><div class="num"><?= $active_quests ?></div></div>
  <div class="kpi"><div class="title">Total Users</div><div class="num"><?= $total_users ?></div></div>
  <div class="kpi"><div class="title">Approval Rate</div><div class="num"><?= $approval_rate ?>%</div></div>
</div>

</div>

<?php require_once '../includes/footer.php'; ?>
