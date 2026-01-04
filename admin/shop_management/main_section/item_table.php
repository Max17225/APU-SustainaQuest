<!-- admin/shop_management/main_section/shop_table.php -->
<?php
$stmt = $conn->query("
    SELECT 
        i.itemId,
        i.itemName,
        i.itemDesc,
        i.itemPictureURL,
        i.quantity,
        i.itemType,
        i.pointCost,
        i.availableStatus,

        COUNT(r.redemptionId) AS totalRedeem,
        SUM(r.redempStatus = 1) AS completedRedeem,
        SUM(r.redempStatus = 0) AS pendingRedeem,

        SUM(
            CASE 
                WHEN i.itemType = 'limited' AND i.availableStatus = 1 THEN 1
                ELSE 0
            END
        ) OVER () AS totalLimitedAvailable,

        SUM(
            CASE 
                WHEN i.itemType = 'permanent' THEN 1
                ELSE 0
            END
        ) OVER () AS totalPermanentRecord

    FROM items i
    LEFT JOIN redemptions r
        ON r.itemId = i.itemId

    GROUP BY i.itemId
");

$items = $stmt->fetch_all(MYSQLI_ASSOC);

$limitedAvailableCount = 0;
$permanentRecordCount  = 0;

if (!empty($items)) {
    $limitedAvailableCount = $items[0]['totalLimitedAvailable'];
    $permanentRecordCount  = $items[0]['totalPermanentRecord'];
}
?>

<div class="management shop">

    <!-- Create / Delete -->
    <div class="create-delete-option">
        <button id="addBtn" class="add-btn">Add New +</button>
        <button id="deleteBtn" class="del-btn">Delete -</button>
    </div>

    <!-- Table -->
    <div class="management-table">
        <div class="table-wrapper">
            <div class="record-table">
                <table>
                    <thead>
                        <tr>
                            <th>All <input type="checkbox" id="selectAll" class="check-all"></th>
                            <th>Item</th>
                            <th class="small-col">Quantity</th>
                            <th class="small-col">Point Cost</th>
                            <th class="small-col">Type</th>
                            <th class="small-col">Redeemed</th>

                            <th class="small-col" id="statusHeader">
                                Status
                                <br>

                                <span class="status-count status-permanent">
                                    Total: <?= $permanentRecordCount ?>/8
                                </span>

                                <span class="status-count status-limited" style="display:none;">
                                    Available: <?= $limitedAvailableCount ?>/8
                                </span>
                            </th>

                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $i): ?>
                            <tr
                                data-id="<?= $i['itemId'] ?>"
                                data-name="<?= strtolower($i['itemName']) ?>"
                                data-quantity="<?= $i['quantity'] ?>"
                                data-point="<?= $i['pointCost'] ?>"
                                data-type="<?= strtolower($i['itemType']) ?>"
                                data-total="<?= $i['totalRedeem'] ?>"
                                data-status="<?= $i['availableStatus'] ?>"
                            >
                                <td>
                                    <input type="checkbox" class="row-check">
                                </td>

                                <td>
                                    <div class="item-cell">
                                        <?php if ($i['itemPictureURL']): ?>
                                            <img src="/APU-SustainaQuest/<?= $i['itemPictureURL'] ?>" class="item-thumb">
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($i['itemName']) ?></span>
                                    </div>
                                </td>

                                <td class="small-col"><?= $i['quantity'] ?></td>
                                <td class="small-col"><?= $i['pointCost'] ?></td>
                                <td class="small-col"><?= $i['itemType'] ?></td>
                                <td class="small-col"><?= $i['totalRedeem'] ?></td>
                                <td class="small-col">
                                    <?= $i['availableStatus'] ? 'Available' : 'Unavailable' ?>
                                </td>

                                <td>
                                    <button class="edit-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m5 16l-1 4l4-1L19.586 7.414a2 2 0 0 0 0-2.828l-.172-.172a2 2 0 0 0-2.828 0zM15 6l3 3m-5 11h8"/></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
    
    <div class="note-group">
        <p class="note">
            Note: Permanent items are always available. Total records must not exceed 8.
        </p>
        <p class="note">
            Note: Limited items may have more than 8 records, but only up to 8 can be available at a time.
        </p>
    </div>

</div>