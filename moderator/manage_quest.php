<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once 'mod_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only a logged-in Moderator can access this page
require_role('moderator');

// Removed user header include to use moderator dashboard styling
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);

// Handle Rotation Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rotate_quests'])) {
    rotate_quests($conn);
    $_SESSION['notice'] = 'Quests rotated successfully! 5 Daily and 3 Weekly quests are now active.';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quest'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'Daily';
    $pointReward = intval($_POST['pointReward'] ?? 0);
    $expReward = intval($_POST['expReward'] ?? 0);
    $questIconURL = null;
    $notice = '';

    if ($title === '' || $description === '') {
        $notice = 'Title and description are required.';
    } else {
        // Handle File Upload
        if (isset($_FILES['questIcon']) && $_FILES['questIcon']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/image/quests/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileTmpPath = $_FILES['questIcon']['tmp_name'];
            $fileName = $_FILES['questIcon']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $questIconURL = 'assets/image/quests/' . $newFileName;
                } else {
                    $notice = 'Error moving uploaded file.';
                }
            } else {
                $notice = 'Invalid file type. Allowed: jpg, png, gif, webp.';
            }
        }

        if (empty($notice)) {
            $modId = $_SESSION['user_id'] ?? null;
            if (create_quest($conn, $title, $description, $type, $pointReward, $expReward, $modId, $questIconURL)) {
                $notice = 'Quest created successfully (Added to pool).';
            } else {
                $notice = 'Failed to create quest.';
            }
        }
    }
    $_SESSION['notice'] = $notice;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_quest'])) {
    $questId = intval($_POST['quest_id'] ?? 0);
    $modId = $_SESSION['user_id'] ?? 0;

    if (delete_quest($conn, $questId, $modId, 'Manual deletion from dashboard')) {
        $notice = 'Quest deleted successfully.';
    } else {
        $notice = 'Failed to delete quest.';
    }
    $_SESSION['notice'] = $notice;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$quests = fetch_all_quests($conn);

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Quests</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{
      --bg:#0b1220; --panel:rgba(255,255,255,.06); --panel2:rgba(255,255,255,.08);
      --text:rgba(255,255,255,.92); --muted:rgba(255,255,255,.65);
      --border:rgba(255,255,255,.14); --shadow:0 10px 30px rgba(0,0,0,.35); --r:16px;
      --orange:#f97316; --green:#34d399; --red:#fb7185;
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
    .container{max-width:1200px;margin:18px auto 40px;padding:0 16px}
    .card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow);padding:20px;margin-bottom:20px}
    h1{margin:0 0 6px;font-size:22px;font-weight:950}
    h2{font-size:18px; margin-top:0;}
    p{color:var(--muted);margin:0 0 12px}
    .alert{padding:10px 12px;border-radius:12px;border:1px solid var(--border);margin-bottom:12px;background:rgba(52,211,153,.12);border-color:rgba(52,211,153,.25);color:#fff}
    
    /* Form Styles */
    label{display:block;margin-top:12px;font-weight:700;font-size:13px;color:var(--muted)}
    input, select, textarea{width:100%;padding:10px 12px;border-radius:12px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);color:#fff;margin-top:6px;font-family:inherit}
    button{margin-top:15px;padding:10px 20px;border-radius:12px;border:1px solid transparent;color:#fff;font-weight:800;cursor:pointer;background:var(--green);border-color:rgba(52,211,153,.35)}
    button:hover{opacity:0.9}
    .rotate-btn{background:var(--orange);border-color:rgba(249,115,22,.35)}
    .delete-btn{background:var(--red);border-color:rgba(251,113,133,.35);padding:6px 12px;font-size:12px;margin-top:0;}
    .delete-btn:hover{background:rgba(251,113,133,.8);}

    /* Table Styles */
    table{width:100%;border-collapse:separate;border-spacing:0 10px}
    th{color:var(--muted);text-align:left;font-size:13px;padding:0 10px}
    td{background:rgba(255,255,255,.05);border:1px solid var(--border);padding:12px 10px;vertical-align:middle}
    tr td:first-child{border-top-left-radius:14px;border-bottom-left-radius:14px}
    tr td:last-child{border-top-right-radius:14px;border-bottom-right-radius:14px}
  </style>
</head>
<body>

<div class="topbar">
  <div class="brand">SustainaQuest</div>
  <div class="nav">
    <a href="mod_dashboard.php">Home</a>
    <a href="verify_submissions.php">Submissions</a>
    <a class="primary" href="manage_quest.php">Quests</a>
    <a href="manage_users.php">Users</a>
    <a href="mod_profile.php">Profile</a>
    <a href="mod_recent_activity.php">Activity</a>
    <a class="logout" href="../includes/logout.php">Logout</a>
  </div>
</div>

<div class="container">

    <?php if ($notice): ?>
        <div class="alert"><?= e($notice) ?></div>
    <?php endif; ?>

    <div class="card">
        <h1>Manage Quests</h1>
        <p>Create new quests and manage the rotation pool.</p>
    </div>

    <div class="card">
        <h2>Quest Rotation</h2>
        <p>The system automatically rotates quests daily and weekly. Use the button below only if you need to <strong>force</strong> a rotation immediately.</p>
        <form method="POST" onsubmit="return confirm('Are you sure? This will reset all active quests and pick new random ones.');">
            <button type="submit" name="rotate_quests" class="rotate-btn">Force Randomize Quests</button>
        </form>
    </div>

    <div class="card">
        <h2>Create Quest</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Title</label>
            <input type="text" name="title" required>
            
            <label>Description</label>
            <textarea name="description" rows="4" required></textarea>
            
            <label>Type</label>
            <select name="type">
                <option value="Daily">Daily</option>
                <option value="Weekly">Weekly</option>
            </select>
            
            <label>Quest Icon (Optional)</label>
            <input type="file" name="questIcon" accept="image/*">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label>Point Reward</label>
                    <input type="number" name="pointReward" value="0">
                </div>
                <div>
                    <label>EXP Reward</label>
                    <input type="number" name="expReward" value="0">
                </div>
            </div>
            
            <button type="submit" name="create_quest">Create Quest</button>
        </form>
    </div>

    <div class="card">
        <h2>Existing Quests</h2>
        <?php if (empty($quests)): ?>
            <p>No quests found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Icon</th><th>Title</th><th>Type</th><th>Points</th><th>EXP</th><th>Active</th><th>Created</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($quests as $q): ?>
                    <tr>
                        <td>
                            <?php if (!empty($q['questIconURL'])): ?>
                                <img src="../<?= e($q['questIconURL']) ?>" alt="Icon" style="width:32px;height:32px;object-fit:contain;">
                            <?php else: ?>
                                <span style="color:var(--muted);font-size:12px;">No Icon</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($q['title']) ?></td>
                        <td><?= e($q['type']) ?></td>
                        <td><?= (int)$q['pointReward'] ?></td>
                        <td><?= (int)$q['expReward'] ?></td>
                        <td><?= $q['isActive'] ? '<span style="color:#34d399;font-weight:bold;">Yes</span>' : '<span style="color:var(--muted);">No</span>' ?></td>
                        <td><?= e($q['createDate']) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this quest?');" style="margin:0;">
                                <input type="hidden" name="quest_id" value="<?= $q['questId'] ?>">
                                <button type="submit" name="delete_quest" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>