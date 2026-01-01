<!-- admin/admin_dashboard/detail_panel/redemption_detail.php -->

<?php
// This file is loaded via AJAX(Not connect with global scope) to show details in the detail panel.
// Need to require necessary files for DB connection and session check. 
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/session_check.php';
session_start();
require_role('admin');


$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT
        r.redemptionId,
        r.redempQuantity,
        r.redempStatus,
        r.redempDate,

        u.userId,
        u.userName,
        u.email,

        i.itemId,
        i.itemName,
        i.itemDesc,
        i.itemPictureURL,
        i.itemType,
        i.pointCost

    FROM redemptions r
    INNER JOIN users u ON r.userId = u.userId
    INNER JOIN items i ON r.itemId = i.itemId
    WHERE r.redemptionId = ?
");

$stmt->bind_param('i', $id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo '<p>Redemption not found.</p>';
    exit;
}

$itemImagePath = '/APU-SustainaQuest/' . ltrim($data['itemPictureURL'], '/');
$statusText = $data['redempStatus'] ? 'Redeemed' : 'Pending';
?>

<div class="redemption-detail">

    <!-- Item info -->
    <div class="item-info">
        <img src="<?= htmlspecialchars($itemImagePath) ?>"
             alt="Item Image"
             class="item-image">

        <h3>Item: <?= htmlspecialchars($data['itemName']) ?></h3>

        <p class="description">
            <?= nl2br(htmlspecialchars($data['itemDesc'])) ?>
        </p>

        <p>Type: <?= htmlspecialchars($data['itemType']) ?></p>
        <p>Point Cost: <?= (int)$data['pointCost'] ?></p>
    </div>

    <hr>

    <!-- Redemption info -->
    <div class="redemption-info">
        <h3>Redemption Details</h3>

        <p>Redemption ID: <?= (int)$data['redemptionId'] ?></p>
        <p>Quantity: <?= (int)$data['redempQuantity'] ?></p>
        <p>Status: <?= htmlspecialchars($statusText) ?></p>
        <p>Date: <?= htmlspecialchars($data['redempDate']) ?></p>
    </div>

    <hr>

    <!-- User info -->
    <div class="user-info">
        <h3>User Info</h3>

        <p>User ID: <?= (int)$data['userId'] ?></p>
        <p>Username: <?= htmlspecialchars($data['userName']) ?></p>
        <p>Email: <?= htmlspecialchars($data['email']) ?></p>
    </div>

</div>
