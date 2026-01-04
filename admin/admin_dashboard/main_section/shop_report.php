<!-- admin/admin_dashboard/main_section/shop_report.php -->

<?php
$year = date('Y');

/* ===============================
   Pending Redemptions
   =============================== */
$stmt = $conn->query("
    SELECT COUNT(*) AS total
    FROM redemptions
    WHERE redempStatus = 0
");
$pendingRedemption = (int)$stmt->fetch_assoc()['total'];

/* ===============================
   Permanent Item Quantity
   (always available)
   =============================== */
$stmt = $conn->query("
    SELECT COALESCE(SUM(quantity), 0) AS total
    FROM items
    WHERE itemType = 'Permanent'
");
$permanentQty = (int) $stmt->fetch_assoc()['total'];

/* ===============================
   Limited Item Quantity (Available)
   =============================== */
$stmt = $conn->query("
    SELECT COALESCE(SUM(quantity), 0) AS total
    FROM items
    WHERE itemType = 'Limited'
      AND availableStatus = 1
");
$limitedQty = (int)$stmt->fetch_assoc()['total'];

/* ===============================
   Low Stock Items (qty < 5)
   =============================== */
$stmt = $conn->query("
    SELECT itemPictureURL
    FROM items
    WHERE quantity < 5
      AND availableStatus = 1
");
$lowStockItems = $stmt->fetch_all(MYSQLI_ASSOC);

/* ===============================
   Redemption Line Graph (per month)
   =============================== */
$redemptionData = array_fill(1, 12, 0);

$stmt = $conn->prepare("
    SELECT MONTH(redempDate) AS month, COUNT(*) AS total
    FROM redemptions
    WHERE YEAR(redempDate) = ?
    GROUP BY MONTH(redempDate)
");
$stmt->bind_param('i', $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $redemptionData[(int)$row['month']] = (int)$row['total'];
}

/* ===============================
   Redemption Activity Table
   =============================== */
$stmt = $conn->query("
    SELECT
        r.redemptionId,
        u.userName,
        i.itemName,
        r.redempQuantity,
        r.redempStatus,
        r.redempDate
    FROM redemptions r
    JOIN users u ON r.userId = u.userId
    JOIN items i ON r.itemId = i.itemId
    ORDER BY r.redempDate DESC
    LIMIT 10
");

$redemptions = $stmt->fetch_all(MYSQLI_ASSOC);

?>

<div class="admin-dashboard shop">

   <div class="report-panel">
        <div class="left-panel">

            <!-- Pending Redemption -->
            <div class="pending-redemption number-card card">
                <h3 class="card-title">Pending Redemption</h3>
                <p class="value"><?= $pendingRedemption ?></p>
            </div>

            <!-- Permanent Item Quantity -->
            <div class="permanent-quantity number-card card">
                <h3 class="card-title">Permanent Total Quantity</h3>
                <p class="value"><?= $permanentQty ?></p>
            </div>

            <!-- Limited Item Quantity -->
            <div class="limited-quantity number-card card">
                <h3 class="card-title">Limited Total Quantity</h3>
                <p class="value"><?= $limitedQty ?></p>
            </div>

            <!-- Low Stock Items(Quantity Below 5)(Available Item) -->
            <div class="low-stock-items card">
                <h3 class="card-title">Low Stock Items</h3>

                <div class="item-frame">
                    <?php foreach ($lowStockItems as $item): ?>
                        <img
                            src="/APU-SustainaQuest/<?= htmlspecialchars($item['itemPictureURL']) ?>"
                            alt="Item"
                        >
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <div class="right-panel">
            <!-- Redemption Line Graph in this year -->
            <div class="redemption-line-graph card">
                <h3 class="card-title">Redemption in <?= $year ?></h3>
                <canvas id="redemptionLineChart"></canvas>
            </div>

            <!-- Redemption Activity -->
            <div class="redemption-activity card">
                <h3 class="card-title">Redemption Activity</h3>

                <div class="table-wrapper">
                    <table class="record-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th class="date-column">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($redemptions as $row): ?>
                                <tr class="click-row" data-type="redemption" data-id="<?= $row['redemptionId'] ?>">
                                    <td><?= htmlspecialchars($row['userName']) ?></td>
                                    <td><?= htmlspecialchars($row['itemName']) ?></td>
                                    <td><?= (int)$row['redempQuantity'] ?></td>
                                    <td>
                                        <?= $row['redempStatus'] ? 'Redeemed' : 'Pending' ?>
                                    </td>
                                    <td class="date-column"><?= date('Y-m-d', strtotime($row['redempDate'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
window.__CHART_DATA__ = window.__CHART_DATA__ || {};
window.__CHART_DATA__.redemption = <?= json_encode(array_values($redemptionData)) ?>;
</script>