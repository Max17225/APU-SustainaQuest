<?php
// moderator/profile.php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/db_connect.php";

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

$moderator_id = (int)$_SESSION['user_id'];

$status_msg  = $_SESSION['status_msg'] ?? null;
$status_type = $_SESSION['status_type'] ?? null;
unset($_SESSION['status_msg'], $_SESSION['status_type']);

// Fetch moderator details
$stmt = $conn->prepare("SELECT moderatorId, modName, email, phoneNumber FROM moderators WHERE moderatorId = ? LIMIT 1");
$stmt->bind_param("i", $moderator_id);
$stmt->execute();
$mod = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mod) {
    session_destroy();
    header("Location: ../auth_page/login.php");
    exit();
}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Moderator Profile</title>
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
    .nav a.primary{background:rgba(34,211,238,.14);border-color:rgba(34,211,238,.28);color:var(--text)}
    .nav a.logout{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.25);color:var(--text)}
    .container{max-width:900px;margin:18px auto 40px;padding:0 16px}
    .card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow);padding:16px;margin-bottom:12px}
    h1{margin:0 0 6px;font-size:22px;font-weight:950}
    .muted{color:var(--muted)}
    label{display:block;margin-top:12px;font-weight:800;font-size:14px;color:rgba(255,255,255,.8)}
    input{width:100%;padding:10px 12px;border-radius:12px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);color:#fff;margin-top:6px}
    input[readonly]{opacity:.85}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media(max-width:700px){.row{grid-template-columns:1fr}}
    .btn{margin-top:14px;padding:10px 14px;border-radius:12px;border:1px solid rgba(34,211,238,.35);background:rgba(34,211,238,.15);color:#fff;font-weight:950;cursor:pointer}
    .btn:hover{background:rgba(34,211,238,.22)}
    .alert{padding:10px 12px;border-radius:12px;border:1px solid var(--border);margin-bottom:12px}
    .success{background:rgba(52,211,153,.12);border-color:rgba(52,211,153,.25)}
    .warning{background:rgba(251,191,36,.12);border-color:rgba(251,191,36,.25)}
    .error{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.25)}
    hr{border:0;border-top:1px solid rgba(255,255,255,.10);margin:16px 0}
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
    <a class="primary" href="mod_profile.php">Profile</a>
    <a href="mod_recent_activity.php">Activity</a>
    <a class="logout" href="../includes/logout.php">Logout</a>
  </div>
</div>

<div class="container">

  <?php if ($status_msg): ?>
    <div class="alert <?= e($status_type ?? 'success') ?>"><?= e($status_msg) ?></div>
  <?php endif; ?>

  <div class="card">
    <h1>My Profile</h1>
    <p class="muted">Update your contact details. Password change is optional.</p>
  </div>

  <div class="card">
    <form action="mod_profile_process.php" method="POST">

      <div class="row">
        <div>
          <label>Moderator ID</label>
          <input type="text" value="<?= e($mod['moderatorId']) ?>" readonly>
        </div>
        <div>
          <label>Username</label>
          <input type="text" value="<?= e($mod['modName']) ?>" readonly>
        </div>
      </div>

      <div class="row">
        <div>
          <label>Email</label>
          <input type="email" name="email" value="<?= e($mod['email']) ?>" required>
        </div>
        <div>
          <label>Phone Number</label>
          <input type="text" name="phoneNumber" value="<?= e($mod['phoneNumber']) ?>" required>
        </div>
      </div>

      <hr>

      <p class="muted" style="margin:0 0 8px;">Change Password (optional)</p>

      <label>Current Password</label>
      <input type="password" name="current_password" placeholder="Leave blank if not changing password">

      <label>New Password</label>
      <input type="password" name="new_password" placeholder="Leave blank if not changing password">

      <label>Confirm New Password</label>
      <input type="password" name="confirm_password" placeholder="Leave blank if not changing password">

      <button class="btn" type="submit">Save Changes</button>
    </form>
  </div>

</div>
</body>
</html>