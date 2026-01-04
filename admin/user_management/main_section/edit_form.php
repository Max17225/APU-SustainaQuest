<!-- admin/user_management/main_section/edit_form.php -->

<?php
require_once __DIR__ . '/../../../includes/db_connect.php';

$type = $_GET['page'] ?? 'user';
$isMod = ($type === 'mod');
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo "Invalid ID";
    exit;
}

if ($isMod) {
    $stmt = $conn->prepare("
        SELECT modName, email, phoneNumber
        FROM moderators
        WHERE moderatorId = ?
    ");
} else {
    $stmt = $conn->prepare("
        SELECT userName, email, level, greenPoints, isBanned
        FROM users
        WHERE userId = ?
    ");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Record not found";
    exit;
}

$nameField = $isMod ? 'modName' : 'userName';
?>

<div class="management form">

    <h2><?= $isMod ? 'Edit Moderator' : 'Edit User' ?></h2>

    <form method="POST"
        action="/APU-SustainaQuest/admin/process/update.php"
        class="edit-form"
        data-mode="edit"
        novalidate>

        <input type="hidden" name="entity" value="<?= $isMod ? 'mod' : 'user' ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <!-- NAME -->
        <div class="form-group">
            <label>Name</label>
            <div class="input-hint">
                <input
                    type="text"
                    name="<?= $nameField ?>"
                    value="<?= htmlspecialchars($data[$nameField]) ?>"
                    data-validate="username"
                    data-original="<?= htmlspecialchars($data[$nameField]) ?>"
                    required
                >
                <small class="hint"></small>
            </div>
        </div>

        <!-- EMAIL -->
        <div class="form-group">
            <label>Email</label>
            <div class="input-hint">
                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($data['email']) ?>"
                    data-validate="email"
                    data-original="<?= htmlspecialchars($data['email']) ?>"
                    required
                >
                <small class="hint"></small>
            </div>
        </div>

        <!-- PASSWORD -->
        <div class="form-group">
            <label>Password</label>
            <div class="input-hint">
                <input
                    type="password"
                    name="password"
                    data-validate="password"
                    placeholder="Leave blank to keep current password"
                >
                <small class="hint"></small>
            </div>
        </div>

        <!-- USER ONLY -->
        <?php if (!$isMod): ?>
            <div class="form-group">
                <label>Level</label>
                <input
                    type="number"
                    name="level"
                    value="<?= $data['level'] ?>"
                    data-original="<?= $data['level'] ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Green Points</label>
                <input
                    type="number"
                    name="greenPoints"
                    value="<?= $data['greenPoints'] ?>"
                    data-original="<?= $data['greenPoints'] ?>"
                    required
                >
            </div>

             <div class="form-group">
                <label>Status</label>

                <div class="status-toggle">
                    <label class="toggle-option">
                        <input
                            class="radio-btn"
                            type="radio"
                            name="isBanned"
                            value="0"
                            data-original="<?= $data['isBanned'] ?>"
                            <?= !$data['isBanned'] ? 'checked' : '' ?>
                        >
                        <span>Normal</span>
                    </label>

                    <label class="toggle-option danger">
                        <input
                            class="radio-btn ban-btn"
                            type="radio"
                            name="isBanned"
                            value="1"
                            data-original="<?= $data['isBanned'] ?>"
                            <?= $data['isBanned'] ? 'checked' : '' ?>
                        >
                        <span>Banned</span>
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <!-- MOD ONLY -->
        <?php if ($isMod): ?>
            <div class="form-group">
                <label>Phone Number</label>
                <input
                    type="text"
                    name="phoneNumber"
                    value="<?= htmlspecialchars($data['phoneNumber']) ?>"
                    data-original="<?= htmlspecialchars($data['phoneNumber']) ?>"
                    required
                >
            </div>
        <?php endif; ?>

        <div class="form-action">
            <button type="submit" class="btn" disabled>Update</button>
            <a href="?module=user&page=<?= $type ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>