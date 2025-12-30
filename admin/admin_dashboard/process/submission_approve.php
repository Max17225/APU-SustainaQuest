<?php
// admin/admin_dashboard/process/submission_approve.php

require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/session_check.php';

session_start();
require_role('admin');

$id = (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare("
    UPDATE questsubmissions
    SET approveStatus = 'Completed',
        verifiedByAdminId = '1',
        verifyDate = NOW()
    WHERE submissionId = ? AND approveStatus = 'Pending'
");
$stmt->bind_param('i', $id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(400);
    exit('Invalid submission.');
}

echo 'Submission approved.';
