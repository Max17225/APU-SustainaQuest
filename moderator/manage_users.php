<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once 'mod_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_role('moderator');

$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($user_id > 0) {
        if ($action === 'ban') {
            if (ban_user($conn, $user_id)) {
                $_SESSION['notice'] = "User #$user_id has been banned.";
            } else {
                $_SESSION['notice'] = "Error banning user.";
            }
        } elseif ($action === 'unban') {
            if (unban_user($conn, $user_id)) {
                $_SESSION['notice'] = "User #$user_id has been unbanned.";
            } else {
                $_SESSION['notice'] = "Error unbanning user.";
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$users = fetch_all_users($conn);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Users</title>
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
    .topbar{position:sticky;top:0;z-index:50;display:flex;align-items:center;gap:15px;padding:14px 18px;background:rgba(10,16,30,.75);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)}
    .brand{font-weight:900}
    .nav{margin-left:auto;display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .nav a{padding:9px 12px;border-radius:999px;border:1px solid transparent;color:var(--muted);font-weight:750;font-size:14px}
    .nav a:hover{background:var(--panel);border-color:var(--border);color:var(--text)}
    .nav a.primary{background:rgba(34,211,238,.14);border-color:rgba(34,211,238,.28);color:var(--text)}
    .nav a.logout{background:rgba(251,113,133,.12);border-color:rgba(251,113,133,.25);color:var(--text)}
    .container{max-width:1200px;margin:18px auto 40px;padding:0 16px}
    .card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow);padding:16px;margin-bottom:12px}
    h1{margin:0 0 6px;font-size:22px;font-weight:950}
    .muted{color:var(--muted);margin:0}
    table{width:100%;border-collapse:separate;border-spacing:0 10px}
    th{color:var(--muted);text-align:left;font-size:13px;padding:0 10px}
    td{background:rgba(255,255,255,.05);border:1px solid var(--border);padding:12px 10px;vertical-align:middle}
    tr td:first-child{border-top-left-radius:14px;border-bottom-left-radius:14px}
    tr td:last-child{border-top-right-radius:14px;border-bottom-right-radius:14px}
    .alert{padding:10px 12px;border-radius:12px;border:1px solid var(--border);margin-bottom:12px;background:rgba(52,211,153,.12);border-color:rgba(52,211,153,.25);color:#fff}
    .btn{padding:6px 10px;border-radius:8px;border:1px solid;font-weight:800;cursor:pointer;font-size:12px;margin:0}
    .btn-ban{background:rgba(251,113,133,.15);border-color:rgba(251,113,133,.35);color:#fb7185}
    .btn-unban{background:rgba(52,211,153,.15);border-color:rgba(52,211,153,.35);color:#34d399}
    .status-banned{color:#fb7185;font-weight:700}
    .status-active{color:#34d399;font-weight:700}
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
  <div class="brand">SustainaQuest</div>
  <div class="nav">
    <a href="mod_dashboard.php">Home</a>
    <a href="verify_submissions.php">Submissions</a>
    <a href="manage_quest.php">Quests</a>
    <a class="primary" href="manage_users.php">Users</a>
    <a href="mod_profile.php">Profile</a>
    <a href="mod_recent_activity.php">Activity</a>
    <a class="logout" href="../includes/logout.php">Logout</a>
  </div>
</div>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
<div class="mobile-sidebar">
  <button class="close-btn" onclick="toggleSidebar()">&times;</button>
  <a href="mod_dashboard.php">Home</a>
  <a href="verify_submissions.php">Submissions</a>
  <a href="manage_quest.php">Quests</a>
  <a href="manage_users.php" class="active">Users</a>
  <a href="mod_profile.php">Profile</a>
  <a href="mod_recent_activity.php">Activity</a>
  <a href="../includes/logout.php" style="color:#fb7185">Logout</a>
</div>

<div class="container">
  <?php if ($notice): ?>
    <div class="alert"><?= e($notice) ?></div>
  <?php endif; ?>

  <div class="card">
    <h1>User Management</h1>
    <p class="muted">View user details and manage their ban status.</p>
  </div>

  <div class="card">
    <div style="overflow-x:auto">
    <table>
      <thead>
        <tr>
          <th>User ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Level</th>
          <th>Points</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($users)): ?>
        <tr><td colspan="7">No users found.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= e($user['userId']) ?></td>
            <td><?= e($user['userName']) ?></td>
            <td><?= e($user['email']) ?></td>
            <td><?= e($user['level']) ?></td>
            <td><?= e($user['greenPoints']) ?></td>
            <td>
              <?php if ($user['isBanned']): ?>
                <span class="status-banned">Banned</span>
              <?php else: ?>
                <span class="status-active">Active</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" style="margin:0;">
                <input type="hidden" name="user_id" value="<?= e($user['userId']) ?>">
                <?php if ($user['isBanned']): ?>
                  <button type="submit" name="action" value="unban" class="btn btn-unban">Unban</button>
                <?php else: ?>
                  <button type="submit" name="action" value="ban" class="btn btn-ban" onclick="return confirm('Are you sure you want to ban this user?');">Ban</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
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