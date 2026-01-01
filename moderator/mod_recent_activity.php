<?php
// moderator/recent_activity.php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/db_connect.php";
require_once "mod_functions.php";

if (session_status() === PHP_SESSION_NONE) session_start();

// Auth
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth_page/login.php");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'moderator') {
    header("Location: ../index.php");
    exit();
}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revert_submission'])) {
    $subId = intval($_POST['submission_id'] ?? 0);
    if ($subId > 0) {
        // This new function is in mod_functions.php
        if (revert_submission_status($conn, $subId)) {
            $_SESSION['notice'] = "Submission #$subId has been reverted to Pending.";
        } else {
            $_SESSION['notice'] = "Error: Could not revert submission.";
        }
    }
    // Redirect to prevent form re-submission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$moderatorId = (int)$_SESSION['user_id'];

// Load latest 25 submissions (newest first)
// Also show who verified (moderator/admin/AI)
$sql = "
    SELECT
        qs.submissionId,
        qs.questId,
        q.title AS questTitle,
        q.type AS questType,
        u.userName AS submittedBy,
        qs.approveStatus,
        qs.submitDate,
        qs.verifyDate,
        qs.verifiedByAi,
        qs.verifiedByModeratorId,
        qs.verifiedByAdminId,
        qs.declinedReason
    FROM questsubmissions qs
    LEFT JOIN quests q ON q.questId = qs.questId
    LEFT JOIN users u ON u.userId = qs.submittedByUserId
    ORDER BY qs.submitDate DESC
    LIMIT 25
";
$res = $conn->query($sql);

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

function badgeClass($status){
    $s = strtolower(trim((string)$status));
    if ($s === 'pending') return 'b-warn';
    if ($s === 'completed' || $s === 'approved') return 'b-ok';
    if ($s === 'rejected') return 'b-danger';
    return 'b-gray';
}

function whoVerified($row){
    // Based on columns: verifiedByAi, verifiedByModeratorId, verifiedByAdminId
    if (!empty($row['verifiedByAi'])) return "AI";
    if (!empty($row['verifiedByModeratorId'])) return "Moderator";
    if (!empty($row['verifiedByAdminId'])) return "Admin";
    return "-";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Recent Activity</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{
      --bg:#0b1220; --panel:rgba(255,255,255,.06); --panel2:rgba(255,255,255,.08);
      --text:rgba(255,255,255,.92); --muted:rgba(255,255,255,.65);
      --border:rgba(255,255,255,.14); --shadow:0 10px 30px rgba(0,0,0,.35); --r:16px;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,Segoe UI,Arial,sans-serif}
    a{text-decoration:none;color:inherit}
    .topbar{position:sticky;top:0;z-index:50;display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:rgba(10,16,30,.75);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)}
    .brand{font-weight:900}
    .nav{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .nav a{padding:9px 12px;border-radius:999px;border:1px solid transparent;color:var(--muted);font-weight:750;font-size:14px}
    .nav a:hover{background:var(--panel);border-color:var(--border);color:var(--text)}
    .nav a.primary{background:rgba(167,139,250,.14);border-color:rgba(167,139,250,.28);color:var(--text)}
    .nav a.logout{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.25);color:var(--text)}
    .container{max-width:1200px;margin:18px auto 40px;padding:0 16px}
    .card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow);padding:16px;margin-bottom:12px}
    h1{margin:0 0 6px;font-size:22px;font-weight:950}
    .muted{color:var(--muted);margin:0}
    table{width:100%;border-collapse:separate;border-spacing:0 10px}
    th{color:var(--muted);text-align:left;font-size:13px;padding:0 10px}
    td{background:rgba(255,255,255,.05);border:1px solid var(--border);padding:12px 10px;vertical-align:top}
    tr td:first-child{border-top-left-radius:14px;border-bottom-left-radius:14px}
    tr td:last-child{border-top-right-radius:14px;border-bottom-right-radius:14px}
    .badge{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:900;border:1px solid var(--border);background:var(--panel2)}
    .b-warn{border-color:rgba(251,191,36,.35);background:rgba(251,191,36,.13)}
    .b-ok{border-color:rgba(52,211,153,.35);background:rgba(52,211,153,.13)}
    .b-danger{border-color:rgba(251,113,133,.35);background:rgba(251,113,133,.13)}
    .b-gray{border-color:rgba(203,213,225,.25);background:rgba(203,213,225,.08)}
    .small{font-size:12px;color:var(--muted);margin-top:4px}
    .reason{margin-top:6px;font-size:12px;color:rgba(255,255,255,.75)}
  </style>
</head>
<body>

<div class="topbar">
  <div class="brand">SustainaQuest</div>
  <div class="nav">
    <a href="mod_dashboard.php">Home</a>
    <a href="verify_submissions.php">Submissions</a>
    <a href="manage_quest.php">Quests</a>
    <a href="manage_users.php">Users</a>
    <a href="mod_profile.php">Profile</a>
    <a class="primary" href="mod_recent_activity.php">Activity</a>
    <a class="logout" href="../includes/logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <?php if ($notice): ?>
    <div class="alert" style="background:rgba(34,211,238,.12); border-color:rgba(34,211,238,.25);"><?= e($notice) ?></div>
  <?php endif; ?>

  <div class="card">
    <h1>Recent Activity</h1>
    <p class="muted">Latest 25 quest submissions (newest first).</p>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Quest</th>
          <th>User</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Verified</th>
          <th>Verifier</th>
        </tr>
      </thead>
      <tbody>
      <?php if (count($rows) === 0): ?>
        <tr><td colspan="6">No activity found.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>
              <strong><?= e($r['questTitle'] ?? 'Unknown Quest') ?></strong>
              <div class="small">
                Quest ID: <?= e($r['questId']) ?> |
                Type: <?= e($r['questType'] ?? '-') ?> |
                Submission ID: <?= e($r['submissionId']) ?>
              </div>

              <?php if (!empty($r['declinedReason'])): ?>
                <div class="reason"><strong>Declined reason:</strong> <?= e($r['declinedReason']) ?></div>
              <?php endif; ?>
            </td>

            <td><?= e($r['submittedBy'] ?? '-') ?></td>

            <td>
              <span class="badge <?= badgeClass($r['approveStatus']) ?>">
                <?= e($r['approveStatus'] ?? '-') ?>
              </span>
              <?php if (in_array($r['approveStatus'], ['Approved', 'Rejected']) && $r['verifiedByAi'] != 1): ?>
                <form method="POST" style="margin-top: 5px;">
                    <input type="hidden" name="submission_id" value="<?= e($r['submissionId']) ?>">
                    <button type="submit" name="revert_submission" style="all:unset; cursor:pointer; font-size:11px; color: #60a5fa; text-decoration:underline;" onclick="return confirm('Are you sure you want to revert this submission to Pending?');">
                        Revert
                    </button>
                </form>
              <?php endif; ?>
            </td>

            <td><?= e($r['submitDate'] ?? '-') ?></td>
            <td><?= e($r['verifyDate'] ?? '-') ?></td>
            <td><?= e(whoVerified($r)) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>