<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once 'mod_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_role('moderator');

$path = '../';
require_once '../includes/header.php';

$msg = '';

// Handle Approval/Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subId = intval($_POST['submission_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $modId = $_SESSION['user_id'];

    if ($subId > 0 && ($action === 'Approve' || $action === 'Reject')) {
        $status = ($action === 'Approve') ? 'Approved' : 'Rejected';
        
        if (update_submission_status($conn, $subId, $status, $modId, $reason)) {
            $msg = "Submission #$subId has been $status.";

            // Award Points & XP if Approved
            if ($status === 'Approved') {
                $stmt = $conn->prepare("SELECT q.pointReward, q.expReward, qs.submittedByUserId FROM questsubmissions qs JOIN quests q ON qs.questId = q.questId WHERE qs.submissionId = ?");
                $stmt->bind_param("i", $subId);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                
                if ($res) {
                    $uid = $res['submittedByUserId'];
                    // Award Green Points
                    $conn->query("UPDATE users SET greenPoints = greenPoints + {$res['pointReward']} WHERE userId = $uid");
                    
                    // Award XP & Check Level Up
                    require_once '../user/user_functions.php';
                    add_xp_and_level_up($conn, $uid, $res['expReward']);
                }
            }
        } else {
            $msg = "Error updating submission.";
        }
    }
}

$submissions = fetch_pending_weekly_submissions($conn);
?>

<div class="container" style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    <h1>Verify Weekly Quests</h1>
    <p>Only Weekly quests require manual verification. Daily quests are verified by AI.</p>

    <?php if ($msg): ?>
        <div class="status-info" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($submissions)): ?>
        <p>No pending weekly submissions found.</p>
    <?php else: ?>
        <div class="submission-list">
            <?php foreach ($submissions as $s): ?>
                <div class="quest-card" style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <div class="header" style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                        <strong>Quest: <?php echo htmlspecialchars($s['title']); ?></strong>
                        <span>User: <?php echo htmlspecialchars($s['userName']); ?></span>
                    </div>
                    
                    <div class="content" style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div class="evidence" style="flex: 1; min-width: 300px;">
                            <h4>Evidence:</h4>
                            <?php if (!empty($s['evidenceVideoURL'])): ?>
                                <video controls style="width: 100%; max-height: 300px; background: #000;">
                                    <source src="<?php echo $path . htmlspecialchars($s['evidenceVideoURL']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <p><small>Video Submission</small></p>
                            <?php elseif (!empty($s['evidencePictureURL'])): ?>
                                <img src="<?php echo $path . htmlspecialchars($s['evidencePictureURL']); ?>" style="width: 100%; max-height: 300px; object-fit: contain; border: 1px solid #ddd;">
                                <p><small>Image Submission</small></p>
                            <?php else: ?>
                                <p>No evidence file found.</p>
                            <?php endif; ?>
                        </div>

                        <div class="details" style="flex: 1;">
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($s['description']); ?></p>
                            <p><strong>Submitted:</strong> <?php echo htmlspecialchars($s['submitDate']); ?></p>
                            
                            <form method="POST" style="margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px;">
                                <input type="hidden" name="submission_id" value="<?php echo $s['submissionId']; ?>">
                                
                                <label style="display: block; margin-bottom: 5px;">Rejection Reason (Optional):</label>
                                <textarea name="reason" rows="2" style="width: 100%; margin-bottom: 10px;"></textarea>
                                
                                <div style="display: flex; gap: 10px;">
                                    <button type="submit" name="action" value="Approve" style="background: #27ae60; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">
                                        Approve
                                    </button>
                                    <button type="submit" name="action" value="Reject" style="background: #c0392b; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">
                                        Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>