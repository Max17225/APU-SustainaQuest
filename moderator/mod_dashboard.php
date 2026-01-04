<?php
// moderator/mod_dashboard.php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/db_connect.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   AUTH / ROLE CHECK
   ========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth_page/login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'moderator') {
    header("Location: ../index.php");
    exit();
}

/* =========================
   KPI QUERIES (Weekly logic)
   ========================= */

// Pending weekly submissions
$sql_pending = "
  SELECT COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE qs.approveStatus = 'Pending'
";
$pending_count = (int) ($conn->query($sql_pending)->fetch_assoc()['total'] ?? 0);

// Approved today (weekly) - Completed + verified today
$sql_approved_today = "
  SELECT COUNT(*) AS total
  FROM questsubmissions qs
  JOIN quests q ON qs.questId = q.questId
  WHERE qs.approveStatus = 'Approved'
    AND q.type = 'Weekly'
    AND qs.verifyDate IS NOT NULL
    AND DATE(qs.verifyDate) = CURDATE()
";
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
  WHERE type = 'Weekly'
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

/* =========================
   HIGHLIGHTS
   ========================= */

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
$peak_label = ($peak_hr === null) ? "-" : sprintf("%02d:00 - %02d:00", $peak_hr, ($peak_hr + 1) % 24);

// For navbar display
$display_name = htmlspecialchars($_SESSION['username'] ?? 'Moderator');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Moderator Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ✅ Inline CSS so you don't need external file paths -->
  <style>
    :root{
      --bg: #0b1220;
      --panel: rgba(255,255,255,0.06);
      --panel2: rgba(255,255,255,0.08);
      --text: rgba(255,255,255,0.92);
      --muted: rgba(255,255,255,0.65);
      --border: rgba(255,255,255,0.14);
      --shadow: 0 10px 30px rgba(0,0,0,0.35);
      --radius: 16px;

      --cyan: #22d3ee;
      --green: #34d399;
      --yellow:#fbbf24;
      --red: #fb7185;
      --purple:#a78bfa;
      --blue:#60a5fa;
      --gray:#cbd5e1;
    }

    *{ box-sizing:border-box; }
    body{
      margin:0;
      background: radial-gradient(900px 500px at 10% 10%, rgba(34,211,238,0.12), transparent 60%),
                  radial-gradient(700px 450px at 80% 20%, rgba(167,139,250,0.12), transparent 65%),
                  var(--bg);
      color: var(--text);
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      min-height:100vh;
    }

    a{ color: inherit; text-decoration:none; }

    .topbar{
      position: sticky;
      top: 0;
      z-index: 50;
      display:flex;
      gap: 15px;
      align-items:center;
      padding: 14px 18px;
      background: rgba(10, 16, 30, 0.75);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border);
    }
    .brand{
      font-weight: 800;
      letter-spacing: 0.4px;
    }
    .brand small{
      display:block;
      font-weight:600;
      color: var(--muted);
      letter-spacing:0;
      margin-top:2px;
      font-size: 12px;
    }

    .nav{
      margin-left: auto;
      display:flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content:flex-end;
      align-items:center;
    }

    .nav a{
      padding: 9px 12px;
      border-radius: 999px;
      border: 1px solid transparent;
      color: var(--muted);
      transition: all .15s ease;
      font-weight: 650;
      font-size: 14px;
    }
    .nav a:hover{
      background: var(--panel);
      border-color: var(--border);
      color: var(--text);
    }
    .nav a.primary{
      background: rgba(34,211,238,0.14);
      border-color: rgba(34,211,238,0.28);
      color: var(--text);
    }
    .nav a.logout{
      background: rgba(251,113,133,0.12);
      border-color: rgba(251,113,133,0.25);
      color: var(--text);
    }

    .container{
      max-width: 1200px;
      margin: 18px auto 40px;
      padding: 0 16px;
    }

    .page-title{
      display:flex;
      align-items:flex-end;
      justify-content:space-between;
      gap: 12px;
      margin: 14px 0 18px;
    }
    .page-title h1{
      margin:0;
      font-size: 22px;
      font-weight: 900;
      letter-spacing: 0.2px;
    }
    .page-title p{
      margin:4px 0 0;
      color: var(--muted);
      font-size: 14px;
    }

    .kpi-grid{
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 20px;
    }

    @media (max-width: 900px){
      .kpi-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 600px){
      .kpi-grid{ grid-template-columns: 1fr; }
    }

    .card{
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 24px;
      position:relative;
      overflow:hidden;
    }
    .card::before{
      content:"";
      position:absolute;
      inset:-2px;
      background: radial-gradient(240px 120px at 20% 0%, rgba(255,255,255,0.10), transparent 60%);
      pointer-events:none;
    }

    .kpi-title{
      color: var(--muted);
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 1px;
    }
    .kpi-num{
      font-size: 30px;
      font-weight: 900;
      margin-top: 8px;
      line-height: 1.05;
    }
    .kpi-sub{
      margin-top: 6px;
      color: var(--muted);
      font-size: 12px;
    }

    .badge{
      position:absolute;
      top: 20px;
      right: 20px;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      border: 1px solid var(--border);
      background: var(--panel2);
      color: var(--text);
    }
    .b-warn{ border-color: rgba(251,191,36,0.35); background: rgba(251,191,36,0.13); }
    .b-ok{ border-color: rgba(52,211,153,0.35); background: rgba(52,211,153,0.13); }
    .b-info{ border-color: rgba(96,165,250,0.35); background: rgba(96,165,250,0.13); }
    .b-cyan{ border-color: rgba(34,211,238,0.35); background: rgba(34,211,238,0.13); }
    .b-danger{ border-color: rgba(251,113,133,0.35); background: rgba(251,113,133,0.13); }
    .b-purple{ border-color: rgba(167,139,250,0.35); background: rgba(167,139,250,0.13); }

    .sections{
      margin-top: 14px;
      display:grid;
      grid-template-columns: 1.3fr 1fr;
      gap: 12px;
    }
    @media (max-width: 900px){
      .sections{ grid-template-columns: 1fr; }
    }

    .section-head{
      display:flex;
      justify-content:space-between;
      align-items:flex-end;
      gap: 10px;
      margin-bottom: 10px;
    }
    .section-head h2{
      margin:0;
      font-size: 16px;
      font-weight: 900;
    }
    .section-head p{
      margin:4px 0 0;
      color: var(--muted);
      font-size: 13px;
    }

    .actions{
      display:grid;
      gap: 10px;
    }
    .action{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding: 12px 12px;
      border-radius: 14px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.05);
      transition: transform .12s ease, background .12s ease;
    }
    .action:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,0.07);
    }
    .action span{
      color: var(--muted);
      font-weight: 700;
      font-size: 13px;
    }
    .pill{
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 900;
      border: 1px solid var(--border);
      background: var(--panel2);
    }
    .pill.blue{ border-color: rgba(96,165,250,0.35); background: rgba(96,165,250,0.13); }
    .pill.green{ border-color: rgba(52,211,153,0.35); background: rgba(52,211,153,0.13); }
    .pill.purple{ border-color: rgba(167,139,250,0.35); background: rgba(167,139,250,0.13); }
    .pill.gray{ border-color: rgba(203,213,225,0.25); background: rgba(203,213,225,0.08); }

    .highlight{
      display:grid;
      gap: 10px;
    }
    .row{
      display:flex;
      justify-content:space-between;
      gap: 10px;
      padding: 12px 12px;
      border-radius: 14px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.05);
      color: var(--text);
    }
    .row .left{ color: var(--text); font-weight: 750; }
    .row .right{ color: var(--muted); font-weight: 750; }

    .footer-note{
      margin-top: 18px;
      color: var(--muted);
      font-size: 12px;
      text-align:center;
    }
    /* Mobile Sidebar */
    .hamburger{display:none;background:none;border:none;color:var(--text);font-size:20px;cursor:pointer;padding:0}
    .sidebar-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;opacity:0;visibility:hidden;transition:.3s}
    .sidebar-overlay.active{opacity:1;visibility:visible}
    .mobile-sidebar{position:fixed;top:0;left:0;bottom:0;width:260px;background:#0b1220;z-index:100;transform:translateX(-100%);transition:.3s;border-right:1px solid var(--border);padding:20px;display:flex;flex-direction:column;gap:10px;margin:0}
    .mobile-sidebar.active{transform:translateX(0)}
    .mobile-sidebar a{padding:12px 16px;border-radius:12px;color:var(--muted);font-weight:700;display:block}
    .mobile-sidebar a:hover,.mobile-sidebar a.active{background:var(--panel);color:var(--text)}
    .mobile-sidebar .close-btn{align-self:flex-end;font-size:24px;background:none;border:none;color:var(--muted);cursor:pointer;margin-bottom:10px}
    @media(max-width:768px){.nav{display:none}.hamburger{display:block}}
  </style>
</head>
<body>

<div class="topbar">
  <button class="hamburger" onclick="toggleSidebar()">&#9776;</button>
  <div class="brand">
    SustainaQuest
    <small>Welcome, <?= $display_name ?></small>
  </div>

  <div class="nav">
    <a class="primary" href="mod_dashboard.php">Home</a>
    <a href="verify_submissions.php">Submissions</a>
    <a href="manage_quest.php">Quests</a>
    <a href="manage_users.php">Users</a>
    <a href="mod_profile.php">Profile</a>
    <a href="mod_recent_activity.php">Activity</a>
    <a class="logout" href="../includes/logout.php">Logout</a>
  </div>
</div>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
<div class="mobile-sidebar">
  <button class="close-btn" onclick="toggleSidebar()">&times;</button>
  <a href="mod_dashboard.php" class="active">Home</a>
  <a href="verify_submissions.php">Submissions</a>
  <a href="manage_quest.php">Quests</a>
  <a href="manage_users.php">Users</a>
  <a href="mod_profile.php">Profile</a>
  <a href="mod_recent_activity.php">Activity</a>
  <a href="../includes/logout.php" style="color:#fb7185">Logout</a>
</div>

<div class="container">
  <div class="page-title">
    <div>
      <h1>Moderator Dashboard</h1>
      <p>Track weekly submissions, approvals, and key platform highlights.</p>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="kpi-grid">
    <div class="card">
      <div class="badge b-warn">Pending</div>
      <div class="kpi-title">Pending Submissions</div>
      <div class="kpi-num"><?= $pending_count ?></div>
      <div class="kpi-sub">Awaiting review</div>
    </div>

    <div class="card">
      <div class="badge b-ok">Today</div>
      <div class="kpi-title">Approved Today</div>
      <div class="kpi-num"><?= $approved_today ?></div>
      <div class="kpi-sub">Completed & verified today</div>
    </div>

    <div class="card">
      <div class="badge b-danger">Today</div>
      <div class="kpi-title">Rejected Today</div>
      <div class="kpi-num"><?= $rejected_today ?></div>
      <div class="kpi-sub">Invalid submissions</div>
    </div>

    <div class="card">
      <div class="badge b-info">Weekly</div>
      <div class="kpi-title">Active Weekly Quests</div>
      <div class="kpi-num"><?= $active_quests ?></div>
      <div class="kpi-sub">Currently available</div>
    </div>

    <div class="card">
      <div class="badge b-cyan">Users</div>
      <div class="kpi-title">Total Participants</div>
      <div class="kpi-num"><?= $total_users ?></div>
      <div class="kpi-sub">Registered users</div>
    </div>

    <div class="card">
      <div class="badge b-purple">Approval</div>
      <div class="kpi-title">Approval Rate</div>
      <div class="kpi-num"><?= $approval_rate ?>%</div>
      <div class="kpi-sub"><?= $completed_count ?> / <?= $total_count ?> completed</div>
    </div>
  </div>

  <div class="sections">
    <!-- Quick Actions -->
    <div class="card">
      <div class="section-head">
        <div>
          <h2>Quick Actions</h2>
          <p>Common moderator tasks</p>
        </div>
      </div>

      <div class="actions">
        <a class="action" href="verify_submissions.php">
          <div>
            <strong>Review Pending Submissions</strong><br>
            <span>Approve or reject weekly submissions</span>
          </div>
          <span class="pill blue"><?= $pending_count ?></span>
        </a>

        <a class="action" href="manage_quest.php">
          <div>
            <strong>Manage Weekly Quests</strong><br>
            <span>Create, edit, or disable quests</span>
          </div>
          <span class="pill green">Go</span>
        </a>

        <a class="action" href="mod_recent_activity.php">
          <div>
            <strong>View Recent Activity</strong><br>
            <span>See Latest Action</span>
          </div>
          <span class="pill purple">Go</span>
        </a>

        <a class="action" href="manage_users.php">
          <div>
            <strong>Manage Users</strong><br>
            <span>View and ban/unban users</span>
          </div>
          <span class="pill gray">Go</span>
        </a>
      </div>
    </div>

    <!-- Highlights -->
    <div class="card">
      <div class="section-head">
        <div>
          <h2>Today's Highlights</h2>
          <p>Key metrics and achievements</p>
        </div>
      </div>

      <div class="highlight">
        <div class="row">
          <div class="left">Most popular quest</div>
          <div class="right">“<?= htmlspecialchars($popular_title) ?>”</div>
        </div>

        <div class="row">
          <div class="left">Completions</div>
          <div class="right"><?= $popular_completions ?></div>
        </div>

        <div class="row">
          <div class="left">Top contributor</div>
          <div class="right"><?= htmlspecialchars($top_user) ?></div>
        </div>

        <div class="row">
          <div class="left">Peak submission time</div>
          <div class="right"><?= htmlspecialchars($peak_label) ?> (<?= $peak_total ?>)</div>
        </div>
      </div>
    </div>
  </div>


<script>
function toggleSidebar(){
  document.querySelector('.mobile-sidebar').classList.toggle('active');
  document.querySelector('.sidebar-overlay').classList.toggle('active');
}
</script>
</body>
</html>