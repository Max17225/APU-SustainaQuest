<!-- admin/shop_management/main_section/edit_form.php -->

<?php
require_once __DIR__ . '/../../../includes/db_connect.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo "Invalid ID";
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        itemName,
        itemDesc,
        itemPictureURL,
        quantity,
        itemType,
        pointCost,
        availableStatus
    FROM items
    WHERE itemId = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Item not found";
    exit;
}
?>

<div class="management form">

    <h2>Edit Item</h2>

    <form method="POST"
        action="/APU-SustainaQuest/admin/process/update.php"
        data-mode="edit"
        enctype="multipart/form-data"
        novalidate>

        <input type="hidden" name="entity" value="items">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="itemType" value="<?= $data['itemType'] ?>">

        <!-- image -->
        <div class="form-group image-group">
            <input type="file" id="icon" name="itemPicture" accept="image/*" hidden>

            <div class="image-frame" id="imageFrame">
                <?php if ($data['itemPictureURL']): ?>
                    <img src="/APU-SustainaQuest/<?= htmlspecialchars($data['itemPictureURL']) ?>" id="previewImg">
                <?php else: ?>
                    <span class="placeholder">Click to upload</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- name -->
        <div class="form-group">
            <label>Item Name</label>
            <input type="text"
                name="itemName"
                value="<?= htmlspecialchars($data['itemName']) ?>"
                data-original="<?= htmlspecialchars($data['itemName']) ?>"
                required>
        </div>

        <!-- descreiption -->
        <div class="form-group">
            <label>Description</label>
            <textarea
                name="itemDesc"
                rows="4"
                data-original="<?= htmlspecialchars($data['itemDesc']) ?>"
                required><?= htmlspecialchars($data['itemDesc']) ?></textarea>
        </div>

        <!-- point cost -->
        <div class="form-group">
            <label>Point Cost</label>
            <input type="number"
                name="pointCost"
                value="<?= $data['pointCost'] ?>"
                data-original="<?= $data['pointCost'] ?>"
                min="1"
                required>
        </div>

        <!-- quantity -->
        <div class="form-group">
            <label>Quantity</label>
            <input type="number"
                name="quantity"
                value="<?= $data['quantity'] ?>"
                data-original="<?= $data['quantity'] ?>"
                min="0"
                required>
        </div>

        <!-- type: cannot be change -->
        <div class="form-group">
            <label>Type</label>
            <div class="readonly-field">
                <?= htmlspecialchars($data['itemType']) ?>
            </div>
        </div>

        <!-- available -->
        <?php if ($data['itemType'] === 'Limited'): ?>
            <div class="form-group">
                <label>Available Status</label>

                <div class="radio-hint-wrapper">
                    <div class="status-toggle">
                        <label class="toggle-option">
                            <input class="radio-btn"
                                type="radio"
                                name="availableStatus"
                                value="1"
                                data-original="<?= $data['availableStatus'] ?>"
                                <?= $data['availableStatus'] ? 'checked' : '' ?>>
                            <span>Available</span>
                        </label>

                        <label class="toggle-option danger">
                            <input class="radio-btn"
                                type="radio"
                                name="availableStatus"
                                value="0"
                                data-original="<?= $data['availableStatus'] ?>"
                                <?= !$data['availableStatus'] ? 'checked' : '' ?>>
                            <span>Unavailable</span>
                        </label>
                    </div>

                    <small class="hint" id="limitHint"></small>
                </div>
                
            </div>
        <?php endif; ?>

        <div class="form-action">
            <button type="submit" class="btn" disabled>Update</button>
            <a href="?module=shop&page=available" class="btn">Cancel</a>
        </div>

    </form>
</div>
