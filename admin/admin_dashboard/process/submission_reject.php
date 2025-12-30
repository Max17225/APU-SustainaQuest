<?php
// admin/admin_dashboard/process/submission_reject.php

require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/session_check.php';

session_start();
require_role('admin');

$id = (int)($_POST['id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

// Validate inputs
if ($id <= 0 || $reason === '') {
    http_response_code(400);
    exit('Invalid input.');
}

$stmt = $conn->prepare("
    UPDATE questsubmissions
    SET approveStatus = 'Rejected',
        verifiedByAdminId = '1',
        verifyDate = NOW(),
        declinedReason = ?
    WHERE submissionId = ? AND approveStatus = 'Pending'
");
$stmt->bind_param('si', $reason, $id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(400);
    exit('Submission already processed or invalid.');
}

echo 'Submission rejected successfully.';
