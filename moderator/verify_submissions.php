<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once 'mod_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_role('moderator');

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Handle Approval/Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = '';
    $sub_id = intval($_POST['submission_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $mod_id = $_SESSION['user_id'];

    if ($sub_id > 0 && in_array($action, ['Approve', 'Reject'])) {
        if ($action === 'Approve') {
            if (approve_quest_submission($conn, $sub_id, $mod_id)) {
                $msg = "Submission #$sub_id has been Approved and points awarded.";
            } else {
                $msg = "Error approving submission.";
            }
        } elseif ($action === 'Reject') {
            if (update_submission_status($conn, $sub_id, 'Rejected', $mod_id, $reason)) {
                $msg = "Submission #$sub_id has been Rejected.";
            } else {
                $msg = "Error rejecting submission.";
            }
        } else {
            $msg = "Error updating submission.";
        }
    }
    $_SESSION['msg'] = $msg;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$submissions = fetch_pending_weekly_submissions($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Verify Submissions</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{
      --bg:#0b1220; --panel:rgba(255,255,255,.06); --panel2:rgba(255,255,255,.08);
      --text:rgba(255,255,255,.92); --muted:rgba(255,255,255,.65);
      --border:rgba(255,255,255,.14); --shadow:0 10px 30px rgba(0,0,0,.35); --r:16px;
      --green:#34d399; --red:#fb7185;
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
    .container{max-width:1000px;margin:18px auto 40px;padding:0 16px}
    .card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow);padding:20px;margin-bottom:20px}
    h1{margin:0 0 6px;font-size:22px;font-weight:950}
    p{color:var(--muted);margin:0 0 12px}
    .alert{padding:10px 12px;border-radius:12px;border:1px solid var(--border);margin-bottom:12px;background:rgba(52,211,153,.12);border-color:rgba(52,211,153,.25);color:#fff}
    
    .sub-header{display:flex;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:10px;margin-bottom:15px}
    .sub-content{display:flex;flex-direction:column;gap:20px}
    .evidence{width:100%}
    .details{width:100%}
    .media-row{display:flex;gap:15px;flex-wrap:wrap}
    .media-row > *{flex:1;min-width:300px}
    
    label{display:block;margin-top:12px;font-weight:700;font-size:13px;color:var(--muted)}
    textarea{width:100%;padding:10px 12px;border-radius:12px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);color:#fff;margin-top:6px;font-family:inherit}
    
    .btn-group{display:flex;gap:10px;margin-top:15px}
    button{padding:10px 20px;border-radius:12px;border:1px solid transparent;color:#fff;font-weight:800;cursor:pointer;flex:1}
    .btn-approve{background:rgba(52,211,153,.15);border-color:rgba(52,211,153,.35);color:#34d399}
    .btn-approve:hover{background:rgba(52,211,153,.22)}
    .btn-reject{background:rgba(251,113,133,.15);border-color:rgba(251,113,133,.35);color:#fb7185}
    .btn-reject:hover{background:rgba(251,113,133,.22)}
  </style>
</head>
<body>

<div class="topbar">
  <div class="brand">SustainaQuest</div>
  <div class="nav">
    <a href="mod_dashboard.php">Home</a>
    <a class="primary" href="verify_submissions.php">Submissions</a>
    <a href="manage_quest.php">Quests</a>
    <a href="manage_users.php">Users</a>
    <a href="mod_profile.php">Profile</a>
    <a href="mod_recent_activity.php">Activity</a>
    <a class="logout" href="../includes/logout.php">Logout</a>
  </div>
</div>

<div class="container">
    <?php if ($msg): ?>
        <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    
    <div class="card" style="text-align:center; padding:10px;">
        <h1 style="margin:0">Verify Weekly Quests</h1>
        <p style="margin:5px 0 0">Only Weekly quests require manual verification. Daily quests are verified by AI.</p>
    </div>

    <?php if (empty($submissions)): ?>
        <div class="card"><p>No pending weekly submissions found.</p></div>
    <?php else: ?>
        <?php foreach ($submissions as $s): ?>
            <div class="card">
                <div class="sub-header">
                    <strong style="font-size:16px;"><?php echo htmlspecialchars($s['title']); ?></strong>
                    <span style="color:var(--muted);">User: <?php echo htmlspecialchars($s['userName']); ?></span>
                </div>
                
                <div class="sub-content">
                    <div class="evidence">
                        <p style="font-weight:700; margin-bottom:8px;">Evidence:</p>
                        <div class="media-row">
                            <?php if (!empty($s['evidenceVideoURL'])): ?>
                                <video controls style="width: 100%; max-height: 300px; background: #000; border-radius:8px;">
                                    <source src="<?php echo '../' . htmlspecialchars($s['evidenceVideoURL']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                            <?php if (!empty($s['evidencePictureURL'])): ?>
                                <img src="<?php echo '../' . htmlspecialchars($s['evidencePictureURL']); ?>" style="width: 100%; max-height: 300px; object-fit: contain; border: 1px solid var(--border); border-radius:8px;">
                            <?php endif; ?>
                        </div>
                        <?php if (empty($s['evidenceVideoURL']) && empty($s['evidencePictureURL'])): ?>
                            <p>No evidence file found.</p>
                        <?php endif; ?>
                    </div>

                    <div class="details">
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($s['description']); ?></p>
                        <p><strong>Submitted:</strong> <?php echo htmlspecialchars($s['submitDate']); ?></p>
                        
                        <form method="POST" style="margin-top: 20px; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border:1px solid var(--border);">
                            <input type="hidden" name="submission_id" value="<?php echo $s['submissionId']; ?>">
                            
                            <label>Rejection Reason (Optional):</label>
                            <textarea name="reason" rows="2"></textarea>
                            
                            <div class="btn-group">
                                <button type="submit" name="action" value="Approve" class="btn-approve">Approve</button>
                                <button type="submit" name="action" value="Reject" class="btn-reject">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>