<?php
// FILE: moderator/mod_functions.php
// DESCRIPTION: Moderator-scoped database helpers (moved from utilities/moderator_functions.php)

// Ensure DB connection ($conn) from includes/db_connect.php is available when calling these.

/**
 * Checks if a 'Weekly' quest is currently active in the database.
 * Enforces the business rule: Only one active weekly quest allowed.
 * @param mysqli $conn
 * @return bool
 */
function is_weekly_quest_active(mysqli $conn): bool {
    $sql = "SELECT COUNT(*) FROM quests WHERE type = 'Weekly' AND isActive = 1";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_row()[0] > 0;
    }
    return false;
}

/**
 * Creates a new quest entry in the database using MySQLi prepared statements.
 */
function create_quest(mysqli $conn, string $title, string $description, string $type, int $points, int $moderator_id): bool {
    $type_db = ($type === 'weekly') ? 'Weekly' : 'Daily';
    $exp_reward = ($type_db === 'Weekly') ? 100 : 50;

    $sql = "INSERT INTO quests 
            (title, description, type, pointReward, expReward, createDate, isActive, createdByModeratorId) 
            VALUES (?, ?, ?, ?, ?, NOW(), 1, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;

    $stmt->bind_param("sssiis", $title, $description, $type_db, $points, $exp_reward, $moderator_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Fetches all quests along with their approved submission counts ('Completions').
 */
function fetch_all_quests(mysqli $conn): array {
    $sql = "
        SELECT 
            q.questId, q.title, q.type, q.pointReward, q.isActive,
            COUNT(CASE WHEN qs.approveStatus = 'Approved' THEN 1 END) AS completions_count
        FROM 
            quests q
        LEFT JOIN 
            questsubmissions qs ON q.questId = qs.questId
        GROUP BY 
            q.questId, q.title, q.type, q.pointReward, q.isActive
        ORDER BY 
            q.isActive DESC, q.questId DESC;";

    $result = $conn->query($sql);
    $quests = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $quests[] = $row;
        }
        $result->free();
    }
    return $quests;
}

?>
