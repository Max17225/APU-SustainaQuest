<?php
// scripts/check_accounts.php
// Usage: php scripts/check_accounts.php <username> <email>
if (php_sapi_name() !== 'cli') {
    echo "Run from CLI only\n";
    exit(1);
}
if ($argc < 3) {
    echo "Usage: php scripts/check_accounts.php <username> <email>\n";
    exit(1);
}
$name = $argv[1];
$email = $argv[2];
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    echo "DB connection not found. Check includes/db_connect.php\n";
    exit(1);
}
function fetchRows($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo "Prepare failed: ({$conn->errno}) {$conn->error}\n"; return null; }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}
$users = fetchRows($conn, "SELECT userId,userName,email FROM users WHERE userName = ? OR email = ? LIMIT 5", "ss", [$name, $email]);
$mods  = fetchRows($conn, "SELECT moderatorId,modName,email FROM moderators WHERE modName = ? OR email = ? LIMIT 5", "ss", [$name, $email]);
echo "Users matching name/email:\n";
if ($users === null) { echo "Query failed.\n"; } elseif (count($users) === 0) { echo "  (none)\n"; } else {
    foreach ($users as $r) {
        echo "  id={$r['userId']} userName={$r['userName']} email={$r['email']}\n";
    }
}
echo "\nModerators matching name/email:\n";
if ($mods === null) { echo "Query failed.\n"; } elseif (count($mods) === 0) { echo "  (none)\n"; } else {
    foreach ($mods as $r) {
        echo "  id={$r['moderatorId']} modName={$r['modName']} email={$r['email']}\n";
    }
}
echo "\n";
exit(0);
