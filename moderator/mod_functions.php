<?php
// moderator/mod_functions.php
// Helper functions used by moderator pages

function create_quest(mysqli $conn, string $title, string $description, string $type, int $pointReward = 0, int $expReward = 0, ?int $moderatorId = null): bool
{
    $sql = "INSERT INTO quests (createdByModeratorId, questIconURL, title, description, type, pointReward, expReward) VALUES (?, NULL, ?, ?, ?, ?, ?)";
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

?>
