<?php
// scripts/simulate_login.php
// Usage: php scripts/simulate_login.php <username_or_email>
if (php_sapi_name() !== 'cli') { echo "Run from CLI only\n"; exit(1); }
if ($argc < 2) { echo "Usage: php scripts/simulate_login.php <username_or_email>\n"; exit(1); }
$input = $argv[1];
require_once __DIR__ . '/../includes/db_connect.php';
if (!isset($conn) || !($conn instanceof mysqli)) { echo "DB connection not found.\n"; exit(1); }
$sql = "
    SELECT *
    FROM (
        SELECT
            moderatorId AS id,
            modName AS username,
            email AS email,
            modPassword AS password,
            'moderator' AS role,
            0 AS isBanned
        FROM moderators

        UNION ALL

        SELECT
            adminId AS id,
            adminName AS username,
            NULL AS email,
            adminPassword AS password,
            'admin' AS role,
            0 AS isBanned
        FROM admins

        UNION ALL

        SELECT 
            userId AS id,
            userName AS username,
            email AS email,
            passwordHash AS password,
            'user' AS role,
            isBanned AS isBanned
        FROM users
    ) AS all_accounts
    WHERE username = ? OR email = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
if (!$stmt) { echo "Prepare failed: ({$conn->errno}) {$conn->error}\n"; exit(1); }
$stmt->bind_param('ss', $input, $input);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) { echo "No matching account.\n"; exit(0); }
echo "Matched account:\n";
echo "  role={$row['role']} id={$row['id']} username={$row['username']} email={$row['email']}\n";
exit(0);
