<?php
// moderator/mod_functions.php
// Helper functions used by moderator pages

function create_quest(mysqli $conn, string $title, string $description, string $type, int $pointReward = 0, int $expReward = 0, ?int $moderatorId = null): bool
{
    // Default isActive to 0 so it enters the pool waiting for rotation
    $sql = "INSERT INTO quests (createdByModeratorId, questIconURL, title, description, type, pointReward, expReward, isActive) VALUES (?, NULL, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('isssii', $moderatorId, $title, $description, $type, $pointReward, $expReward);
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
    $sqlDaily = "UPDATE quests 
                 SET isActive = 1 
                 WHERE type = 'Daily' 
                 ORDER BY RAND() 
                 LIMIT 5";
    $conn->query($sqlDaily);

    // 3. Randomly activate 3 Weekly quests
    $sqlWeekly = "UPDATE quests 
                  SET isActive = 1 
                  WHERE type = 'Weekly' 
                  ORDER BY RAND() 
                  LIMIT 3";
    $conn->query($sqlWeekly);
}

function fetch_pending_weekly_submissions(mysqli $conn): array
{
    // Only fetch Weekly quests that are Pending
    $sql = "SELECT qs.*, q.title, q.description, q.type, u.userName 
            FROM questsubmissions qs
            JOIN quests q ON qs.questId = q.questId
            JOIN users u ON qs.submittedByUserId = u.userId
            WHERE q.type = 'Weekly' 
            AND qs.approveStatus = 'Pending'
            ORDER BY qs.submitDate ASC";
    
    $res = $conn->query($sql);
    if (!$res) return [];
    return $res->fetch_all(MYSQLI_ASSOC);
}

function update_submission_status(mysqli $conn, int $submissionId, string $status, int $modId, string $reason = ''): bool
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
    
    $stmt->bind_param('sissi', $status, $modId, $now, $reason, $submissionId);
    $ok = $stmt->execute();
    $stmt->close();
    
    return (bool)$ok;
}

?>