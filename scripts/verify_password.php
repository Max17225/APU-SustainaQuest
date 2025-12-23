<?php
// scripts/verify_password.php
// Usage: php scripts/verify_password.php <username_or_email> <password>
if (php_sapi_name() !== 'cli') { echo "Run from CLI only\n"; exit(1); }
if ($argc < 3) { echo "Usage: php scripts/verify_password.php <username_or_email> <password>\n"; exit(1); }
 $input = $argv[1];
 $provided_password = $argv[2];
// (debug omitted)
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
if (!$row) { echo "No matching account for '{$input}'.\n"; exit(0); }
echo "Matched account: role={$row['role']} id={$row['id']} username={$row['username']} email={$row['email']}\n";
$hash = $row['password'];
echo "Stored hash: {$hash}\n";
$ok = password_verify($provided_password, $hash) ? 'MATCH' : 'NO MATCH';
echo "password_verify('{$provided_password}') => {$ok}\n";
exit(0);
