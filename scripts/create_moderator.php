<?php
// scripts/create_moderator.php
// Usage: php scripts/create_moderator.php <name> <email> <password>

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

if ($argc < 4) {
    echo "Usage: php scripts/create_moderator.php <name> <email> <password>\n";
    exit(1);
}

$name = $argv[1];
$email = $argv[2];
$plain_password = $argv[3];

require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo "Database connection not found or invalid. Check includes/db_connect.php\n";
    exit(1);
}

$hash = password_hash($plain_password, PASSWORD_BCRYPT);

// If a moderator with this email exists, update the password/name; otherwise insert.
$check = $conn->prepare("SELECT moderatorId FROM moderators WHERE email = ? LIMIT 1");
if (!$check) { echo "Prepare failed: ({$conn->errno}) {$conn->error}\n"; exit(1); }
$check->bind_param('s', $email);
$check->execute();
$res = $check->get_result();
if ($row = $res->fetch_assoc()) {
    $mid = $row['moderatorId'];
    $upd = $conn->prepare("UPDATE moderators SET modName = ?, modPassword = ? WHERE moderatorId = ?");
    if (!$upd) { echo "Prepare failed: ({$conn->errno}) {$conn->error}\n"; exit(1); }
    $upd->bind_param('ssi', $name, $hash, $mid);
    if ($upd->execute()) {
        echo "Moderator updated: {$name} ({$email})\n";
        $upd->close();
        $check->close();
        exit(0);
    } else {
        echo "Update failed: ({$conn->errno}) {$conn->error}\n";
        $upd->close();
        $check->close();
        exit(1);
    }
} else {
    $check->close();
    $stmt = $conn->prepare("INSERT INTO moderators (modName, modPassword, email) VALUES (?, ?, ?)");
    if (!$stmt) { echo "Prepare failed: ({$conn->errno}) {$conn->error}\n"; exit(1); }
    $stmt->bind_param('sss', $name, $hash, $email);
    if ($stmt->execute()) {
        echo "Moderator created: {$name} ({$email})\n";
        $stmt->close();
        exit(0);
    } else {
        if ($conn->errno === 1062) {
            echo "Error: duplicate entry for email ({$email}).\n";
        } else {
            echo "Insert failed: ({$conn->errno}) {$conn->error}\n";
        }
        $stmt->close();
        exit(1);
    }
}