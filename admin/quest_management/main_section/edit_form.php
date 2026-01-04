<!-- admin/quest_management/main_section/edit_form.php -->

<?php
require_once __DIR__ . '/../../../includes/db_connect.php';

$page = $_GET['page'] ?? 'available';
$isDeleted = ($page === 'deleted');
$readonly = $isDeleted ? 'disabled' : '';
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo "Invalid ID";
    exit;
}

if ($isDeleted) {
    $stmt = $conn->prepare("
        SELECT q.title, q.questIconURL, q.description, q.pointReward, q.expReward, q.type,
               d.reason
        FROM questDelete d
        JOIN quests q ON q.questId = d.questId
        WHERE q.questId = ?
    ");
} else {
    $stmt = $conn->prepare("
        SELECT title, questIconURL, description, pointReward, expReward, type
        FROM quests
        WHERE questId = ?
    ");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Record not found";
    exit;
}

?>

<div class="management form">

    <h2><?= $isDeleted ? 'Review Deleted Quest' : 'Edit Quest' ?></h2>

    <form method="POST"
        action="/APU-SustainaQuest/admin/process/update.php"
        data-mode="edit"
        enctype="multipart/form-data"
        novalidate>

        <input type="hidden" name="entity" value="quests">
        <input type="hidden" name="id" value="<?= $id ?>">

        <!-- ICON -->
        <div class="form-group image-group">
            <!-- hidden file input -->
            <input
                type="file"
                id="icon"
                name="questIcon"
                accept="image/*"
                hidden
                <?= $readonly ?>
            >

            <!-- clickable preview frame -->
            <div class="image-frame" id="imageFrame">
                <?php if (!empty($data['questIconURL'])): ?>
                    <img
                        id="previewImg"
                        src="/APU-SustainaQuest/<?= htmlspecialchars($data['questIconURL']) ?>"
                        alt="Quest Icon"
                    >
                <?php else: ?>
                    <span class="placeholder">Click to upload</span>
                    <img id="previewImg" hidden>
                <?php endif; ?>
            </div>
        </div>

        <!-- TITLE -->
        <div class="form-group">
            <label>Title</label>
            <div class="input-hint">
                <input type="text"
                    name="title"
                    value="<?= htmlspecialchars($data['title']) ?>"
                    data-original="<?= htmlspecialchars($data['title']) ?>"
                    data-validate="questTitle"
                    required
                    <?= $readonly ?>
                    >
                <small class="hint"></small>
            </div>
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group">
            <label>Description</label>
            <textarea type="text"
                    name="description"
                    rows="4"
                    data-original="<?= htmlspecialchars($data['description']) ?>"
                    required <?= $readonly ?> ><?= htmlspecialchars($data['description']) ?></textarea>
        </div>

        <!-- POINT -->
        <div class="form-group">
            <label>Point Reward</label>
            <input type="number"
                name="pointReward"
                value="<?= $data['pointReward'] ?>"
                data-original="<?= $data['pointReward'] ?>"
                min="1"
                required
                <?= $readonly ?>
                >
        </div>

        <!-- EXP -->
        <div class="form-group">
            <label>EXP Reward</label>
            <input type="number"
                name="expReward"
                value="<?= $data['expReward'] ?>"
                data-original="<?= $data['expReward'] ?>"
                min="1"
                required
                <?= $readonly ?>
                >
        </div>

        <!-- TYPE -->
        <div class="form-group">
            <label>Type</label>
            <div class="status-toggle">
                <?php foreach (['Daily','Weekly'] as $t): ?>
                    <label class="toggle-option">
                        <input class="radio-btn"
                            type="radio"
                            name="questType"
                            value="<?= $t ?>"
                            data-original="<?= $data['type'] ?>"
                            <?= $data['type'] === $t ? 'checked' : '' ?>
                            required
                            <?= $readonly ?>
                            >
                        <span><?= $t ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>


        <!-- DELETED QUEST EXTRA -->
        <?php if ($isDeleted): ?>

            <div class="form-group">
                <label>Deletion Reason</label>
                <textarea rows="3" disabled <?= $readonly ?> ><?= htmlspecialchars($data['reason']) ?></textarea>
            </div>

            <div class="form-action">
                <button type="submit"
                        name="restore"
                        value="1"
                        class="btn success">
                    Make Quest Available
                </button>

                <a href="?module=quest&page=<?= $page ?>" class="btn">Cancel</a>
            </div>

        <?php else: ?>

            <div class="form-action">
                <button type="submit" class="btn" disabled>Update</button>
                <a href="?module=quest&page=<?= $page ?>" class="btn">Cancel</a>
            </div>

        <?php endif; ?>

    </form>
</div>
