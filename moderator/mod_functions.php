<?php
// moderator/mod_functions.php
// Helper functions used by moderator pages

function create_quest(mysqli $conn, string $title, string $description, string $type, int $point_reward = 0, int $exp_reward = 0, ?int $moderator_id = null, ?string $quest_icon_url = null): bool
{
    // Default isActive to 0 so it enters the pool waiting for rotation
    $sql = "INSERT INTO quests (createdByModeratorId, questIconURL, title, description, type, pointReward, expReward, isActive) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('issssii', $moderator_id, $quest_icon_url, $title, $description, $type, $point_reward, $exp_reward);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool)$ok;
}

function fetch_all_quests(mysqli $conn): array
{
    $sql = "SELECT * FROM quests ORDER BY createDate DESC";
    $res = $conn->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

function is_weekly_quest_active(mysqli $conn): bool
{
    $sql = "SELECT COUNT(*) AS cnt FROM quests WHERE type = 'Weekly' AND isActive = 1";
    $res = $conn->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return (isset($row['cnt']) && (int)$row['cnt'] > 0);
}

function rotate_quests(mysqli $conn): void
{
    // 1. Deactivate ALL quests
    $conn->query("UPDATE quests SET isActive = 0");

    // 2. Randomly activate 5 Daily quests
    // Logic: Select random Daily quests that are NOT in the questdelete table using JOIN
    $sql_daily = "UPDATE quests 
                 SET isActive = 1 
                 WHERE questId IN (
                     SELECT questId FROM (
                         SELECT q.questId FROM quests q
                         LEFT JOIN questdelete qd ON q.questId = qd.questId
                         WHERE q.type = 'Daily' AND qd.questId IS NULL
                         ORDER BY RAND() LIMIT 5
                     ) AS tmp
                 )";
    $conn->query($sql_daily);

    // 3. Randomly activate 3 Weekly quests
    // Logic: Select random Weekly quests that are NOT in the questdelete table using JOIN
    $sql_weekly = "UPDATE quests 
                  SET isActive = 1 
                  WHERE questId IN (
                     SELECT questId FROM (
                         SELECT q.questId FROM quests q
                         LEFT JOIN questdelete qd ON q.questId = qd.questId
                         WHERE q.type = 'Weekly' AND qd.questId IS NULL
                         ORDER BY RAND() LIMIT 3
                     ) AS tmp
                 )";
    $conn->query($sql_weekly);
}

function fetch_pending_weekly_submissions(mysqli $conn): array
{
    // Fetch all Pending quests (usually Weekly)
    $sql = "SELECT qs.*, q.title, q.description, q.type, IFNULL(u.userName, 'Unknown User') as userName 
            FROM questsubmissions qs
            JOIN quests q ON qs.questId = q.questId
            LEFT JOIN users u ON qs.submittedByUserId = u.userId
            WHERE qs.approveStatus = 'Pending'
            ORDER BY qs.submitDate ASC";
    
    $res = $conn->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

function update_submission_status(mysqli $conn, int $submission_id, string $status, int $mod_id, string $reason = ''): bool
{
    $now = date('Y-m-d H:i:s');
    
    $sql = "UPDATE questsubmissions 
            SET approveStatus = ?, 
                verifiedByModeratorId = ?, 
                verifyDate = ?, 
                declinedReason = ? 
            WHERE submissionId = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    
    $stmt->bind_param('sissi', $status, $mod_id, $now, $reason, $submission_id);
    $ok = $stmt->execute();
    $stmt->close();
    
    return (bool)$ok;
}

function delete_quest(mysqli $conn, int $quest_id, int $mod_id, ?string $reason = null): bool
{
    // 1. Log to questdelete table first
    $stmt = $conn->prepare("INSERT INTO questdelete (questId, deletedByModeratorId, reason) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('iis', $quest_id, $mod_id, $reason);
        $stmt->execute();
        $stmt->close();
    }

    // 2. Delete from quests table
    $stmt = $conn->prepare("DELETE FROM quests WHERE questId = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $quest_id);
    $ok = $stmt->execute();
    $stmt->close();
    
    return (bool)$ok;
}

function ban_user(mysqli $conn, int $user_id): bool
{
    $stmt = $conn->prepare("UPDATE users SET isBanned = 1 WHERE userId = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $user_id);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool)$ok;
}

function unban_user(mysqli $conn, int $user_id): bool
{
    $stmt = $conn->prepare("UPDATE users SET isBanned = 0 WHERE userId = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $user_id);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool)$ok;
}

function fetch_all_users(mysqli $conn): array
{
    $sql = "SELECT userId, userName, email, level, greenPoints, isBanned FROM users ORDER BY userId ASC";
    $res = $conn->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

function approve_quest_submission(mysqli $conn, int $submission_id, int $mod_id): bool
{
    // 1. Update status to Approved
    if (!update_submission_status($conn, $submission_id, 'Approved', $mod_id, '')) {
        return false;
    }

    // 2. Fetch Quest Rewards & User ID
    $stmt = $conn->prepare("
        SELECT qs.submittedByUserId, q.pointReward, q.expReward 
        FROM questsubmissions qs
        JOIN quests q ON qs.questId = q.questId
        WHERE qs.submissionId = ?
    ");
    $stmt->bind_param('i', $submission_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res && $res['submittedByUserId']) {
        $user_id = (int)$res['submittedByUserId'];
        $points = (int)$res['pointReward'];
        $exp = (int)$res['expReward'];

        // 3. Award Green Points & XP
        $conn->query("UPDATE users SET greenPoints = greenPoints + $points WHERE userId = $user_id");
        require_once __DIR__ . '/../user/user_functions.php';
        add_xp_and_level_up($conn, $user_id, $exp);
    }

    return true;
}

function revert_submission_status(mysqli $conn, int $submission_id): bool
{
    $conn->begin_transaction();

    try {
        // 1. Fetch submission details to see if it was approved and what was awarded
        $stmt = $conn->prepare("
            SELECT qs.submittedByUserId, qs.approveStatus, qs.verifiedByAi, q.pointReward, q.expReward
            FROM questsubmissions qs
            JOIN quests q ON qs.questId = q.questId
            WHERE qs.submissionId = ?
        ");
        $stmt->bind_param('i', $submission_id);
        $stmt->execute();
        $submission = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$submission) {
            throw new Exception("Submission not found.");
        }

        // Check if AI verified - prevent revert
        if ($submission['verifiedByAi'] == 1) {
            throw new Exception("Cannot revert AI-verified submissions.");
        }

        // 2. If it was approved, deduct points and XP from the user
        if ($submission['approveStatus'] === 'Approved' && $submission['submittedByUserId']) {
            $user_id = (int)$submission['submittedByUserId'];
            $points = (int)$submission['pointReward'];
            $exp = (int)$submission['expReward'];

            $update_user_stmt = $conn->prepare("UPDATE users SET greenPoints = greenPoints - ?, levelProgress = levelProgress - ? WHERE userId = ?");
            $update_user_stmt->bind_param('iii', $points, $exp, $user_id);
            $update_user_stmt->execute();
            $update_user_stmt->close();
        }

        // 3. Reset the submission status to 'Pending' and clear verification data
        $reset_stmt = $conn->prepare("UPDATE questsubmissions SET approveStatus = 'Pending', verifyDate = NULL, verifiedByModeratorId = NULL, verifiedByAdminId = NULL, declinedReason = NULL WHERE submissionId = ?");
        $reset_stmt->bind_param('i', $submission_id);
        $reset_stmt->execute();
        $reset_stmt->close();

        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage()); // Log error for debugging
        return false;
    }
}

?>