<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

// 1. Security & Session Setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only Moderators can see this
require_role('moderator');

// 2. Include System Header
$path = '../';
$page_css = ""; // You can create a mod_dashboard.css if you want to move styles out
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
$pending_count = (int) ($conn->query($sql_pending)->fetch_assoc()['total'] ?? 0);

// Approved today (weekly)
$sql_approved_today = "
  SELECT COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE qs.approveStatus = 'Approved' 
    AND q.type = 'Weekly'
    AND qs.verifyDate IS NOT NULL
    AND DATE(qs.verifyDate) = CURDATE()
";
// Note: Changed 'Completed' to 'Approved' to match your DB schema in verify_submissions.php
$approved_today = (int) ($conn->query($sql_approved_today)->fetch_assoc()['total'] ?? 0);

// Rejected today (weekly)
$sql_rejected_today = "
  SELECT COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE qs.approveStatus = 'Rejected'
    AND q.type = 'Weekly'
    AND qs.verifyDate IS NOT NULL
    AND DATE(qs.verifyDate) = CURDATE()
";
$rejected_today = (int) ($conn->query($sql_rejected_today)->fetch_assoc()['total'] ?? 0);

// Active quests available (weekly)
$sql_active_quests = "
  SELECT COUNT(*) AS total
  FROM quests
  WHERE type = 'Weekly' AND isActive = 1
";
$active_quests = (int) ($conn->query($sql_active_quests)->fetch_assoc()['total'] ?? 0);

// Total participants (users count)
$sql_total_users = "SELECT COUNT(*) AS total FROM users";
$total_users = (int) ($conn->query($sql_total_users)->fetch_assoc()['total'] ?? 0);

// Approval rate (weekly)
$sql_approval_rate = "
  SELECT
    SUM(CASE WHEN qs.approveStatus='Approved' THEN 1 ELSE 0 END) AS completed_count,
    COUNT(*) AS total_count
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE q.type = 'Weekly'
";
$rate_row = $conn->query($sql_approval_rate)->fetch_assoc();
$completed_count = (int) ($rate_row['completed_count'] ?? 0);
$total_count = (int) ($rate_row['total_count'] ?? 0);
$approval_rate = ($total_count > 0) ? (int) round(($completed_count / $total_count) * 100) : 0;

// -----------------------------
// Highlights
// -----------------------------

// Most popular weekly quest (by completed submissions)
$sql_popular = "
  SELECT q.title, COUNT(*) AS completions
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE q.type = 'Weekly'
    AND qs.approveStatus = 'Approved'
  GROUP BY q.questId
  ORDER BY completions DESC
  LIMIT 1
";
$popular_row = $conn->query($sql_popular)->fetch_assoc();
$popular_title = $popular_row['title'] ?? "-";
$popular_completions = (int) ($popular_row['completions'] ?? 0);

// Top contributor (by completed weekly submissions)
$sql_top_user = "
  SELECT u.userName, COUNT(*) AS total_completed
  FROM questsubmissions qs
  JOIN users u ON qs.submittedByUserId = u.userId
  JOIN quests q ON qs.questId = q.questId
  WHERE q.type = 'Weekly'
    AND qs.approveStatus = 'Approved'
  GROUP BY u.userId
  ORDER BY total_completed DESC
  LIMIT 1
";
$top_user_row = $conn->query($sql_top_user)->fetch_assoc();
$top_user = $top_user_row['userName'] ?? "-";

// Peak submission time (hour block) for today (all submissions)
$sql_peak = "
  SELECT HOUR(submitDate) AS hr, COUNT(*) AS total
  FROM questsubmissions
  WHERE DATE(submitDate) = CURDATE()
  GROUP BY hr
  ORDER BY total DESC
  LIMIT 1
";
$peak_row = $conn->query($sql_peak)->fetch_assoc();
$peak_hr = isset($peak_row['hr']) ? (int) $peak_row['hr'] : null;
$peak_total = (int) ($peak_row['total'] ?? 0);
$peak_label = ($peak_hr === null) ? "-" : sprintf("%02d:00 - %02d:00", $peak_hr, ($peak_hr + 2) % 24);
?>

<!-- Internal Styles for Dashboard (Scoped to not break header) -->
<style>
    :root {
      --card: #ffffff;
      --text: #0f172a;
      --muted: #64748b;
      --primary: #2563eb;
      --success: #16a34a;
      --danger: #dc2626;
      --warning: #f59e0b;
      --purple: #7c3aed;
      --cyan: #06b6d4;
      --shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
      --radius: 16px;
    }

    .dashboard-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .kpi_row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 14px;
      margin-top: 16px;
    }

    .kpi {
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 14px;
      border: 1px solid #e8ecff;
      position: relative;
      overflow: hidden;
      min-height: 110px;
    }

    .kpi:before {
      content: "";
      position: absolute;
      inset: -40px -40px auto auto;
      width: 140px; height: 140px;
      border-radius: 50%;
      opacity: .12;
    }

    .kpi .title { font-size: 13px; color: var(--muted); font-weight: 700; }
    .kpi .num { font-size: 26px; font-weight: 900; margin: 8px 0 4px; color: var(--text); }
    .kpi .sub { font-size: 12px; color: var(--muted); }

    .kpi.pending:before { background: var(--warning); }
    .kpi.approved:before { background: var(--success); }
    .kpi.active:before { background: var(--primary); }
    .kpi.users:before { background: var(--cyan); }
    .kpi.rejected:before { background: var(--danger); }
    .kpi.health:before { background: var(--purple); }

    .kpi .badge {
      position: absolute;
      top: 12px; right: 12px;
      padding: 6px 10px;
      font-size: 12px;
      font-weight: 800;
      border-radius: 999px;
      color: #fff;
    }
    .badge.warn { background: var(--warning); }
    .badge.ok { background: var(--success); }
    .badge.info { background: var(--primary); }
    .badge.cyan { background: var(--cyan); }
    .badge.danger { background: var(--danger); }
    .badge.purple { background: var(--purple); }

    .title_bar {
      margin-top: 16px;
      background: var(--card);
      border: 1px solid #e8ecff;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      text-align: center;
      padding: 12px;
      font-weight: 900;
      font-size: 18px;
      color: var(--text);
    }

    .section {
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      border: 1px solid #e8ecff;
      padding: 16px;
      margin-top: 16px;
    }

    .section_head {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .section h2 { margin: 0; font-size: 18px; font-weight: 900; color: var(--text); }
    .section p { margin: 6px 0 0; color: var(--muted); }

    .quick_actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 12px;
      margin-top: 14px;
    }
    .quick_actions a {
      text-decoration: none;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      padding: 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      color: var(--text);
      font-weight: 800;
      transition: .15s;
    }
    .quick_actions a:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 18px rgba(15,23,42,0.08);
      border-color: #cbd5e1;
    }

    .pill {
      font-size: 12px;
      font-weight: 900;
      padding: 6px 10px;
      border-radius: 999px;
      color: #fff;
      white-space: nowrap;
    }
    .pill.blue { background: var(--primary); }
    .pill.green { background: var(--success); }
    .pill.purple { background: var(--purple); }
    .pill.gray { background: #64748b; }

    .highlight_row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 14px;
      padding: 12px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      margin-top: 10px;
    }
    .highlight_row .left { color: var(--text); font-weight: 700; }
    .highlight_row .right { font-weight: 900; color: var(--text); }
</style>

<div class="dashboard-wrapper">

  <div class="title_bar">Moderator Dashboard</div>

  <!-- KPI Tiles -->
  <div class="kpi_row">
    <div class="kpi pending">
      <div class="badge warn">Pending</div>
      <div class="title">Pending Submission</div>
      <div class="num"><?= $pending_count ?></div>
      <div class="sub">Awaiting Review</div>
    </div>

    <div class="kpi approved">
      <div class="badge ok">Today</div>
      <div class="title">Approve Today</div>
      <div class="num"><?= $approved_today ?></div>
      <div class="sub">Approved Today</div>
    </div>

    <div class="kpi active">
      <div class="badge info">Weekly</div>
      <div class="title">Active Quest</div>
      <div class="num"><?= $active_quests ?></div>
      <div class="sub">Currently Available</div>
    </div>

    <div class="kpi users">
      <div class="badge cyan">Users</div>
      <div class="title">Total Participants</div>
      <div class="num"><?= $total_users ?></div>
      <div class="sub">Active Users</div>
    </div>

    <div class="kpi rejected">
      <div class="badge danger">Today</div>
      <div class="title">Decline Today</div>
      <div class="num"><?= $rejected_today ?></div>
      <div class="sub">Invalid Submission</div>
    </div>

    <div class="kpi health">
      <div class="badge purple">Health</div>
      <div class="title">Platform Health</div>
      <div class="num"><?= $approval_rate ?>%</div>
      <div class="sub">Approval Rate</div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="section">
    <div class="section_head">
      <div>
        <h2>Quick Action</h2>
        <p>Common moderator tasks</p>
      </div>
    </div>

    <div class="quick_actions">
      <a href="verify_submissions.php">
        Review Pending
        <span class="pill blue"><?= $pending_count ?></span>
      </a>

      <a href="manage_quest.php">
        Create New Quest
        <span class="pill green">Go</span>
      </a>

      <a href="#" style="opacity:0.6; cursor:not-allowed;">
        View Recent Activity
        <span class="pill purple">Soon</span>
      </a>

      <a href="#" style="opacity:0.6; cursor:not-allowed;">
        Manage User Profiles
        <span class="pill gray">Soon</span>
      </a>
    </div>
  </div>

  <!-- Today's Highlights -->
  <div class="section">
    <div class="section_head">
      <div>
        <h2>Today's Highlights</h2>
        <p>Key metrics and achievements</p>
      </div>
    </div>

    <div class="highlight_row">
      <div class="left">Most popular quest: "<?= htmlspecialchars($popular_title) ?>"</div>
      <div class="right"><?= $popular_completions ?> completions</div>
    </div>

    <div class="highlight_row">
      <div class="left">Top contributor: <?= htmlspecialchars($top_user) ?></div>
      <div class="right"></div>
    </div>

    <div class="highlight_row">
      <div class="left">Peak submission time: <?= htmlspecialchars($peak_label) ?></div>
      <div class="right"><?= $peak_total ?> submissions</div>
    </div>
  </div>

</div>

<?php require_once '../includes/footer.php'; ?>
