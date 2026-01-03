<!-- admin/user_management/main_section/create_form.php -->

<?php
$type = $_GET['page'] ?? 'user'; // user | mod
$isMod = ($type === 'mod');
$nameField = $isMod ? 'modName' : 'userName';
?>

<div class="management form">

    <h2><?= $isMod ? 'Create Moderator' : 'Create User' ?></h2>

    <form method="POST" action="/APU-SustainaQuest/admin/process/create.php" data-mode="create" novalidate>

        <!-- tell process what to create -->
        <input type="hidden" name="entity" value="<?= $isMod ? 'mod' : 'user' ?>">

        <!-- ======================
             NAME
             ====================== -->
        <div class="form-group">
            <label>Name</label>
            <div class="input-hint">
                <input
                    type="text"
                    name="<?= $nameField ?>"
                    data-validate="username"
                    required
                >
                <small class="hint"></small>
            </div>
        </div>

        <!-- ======================
             EMAIL
             ====================== -->
        <div class="form-group">
            <label>Email</label>
            <div class="input-hint">
                <input
                    type="email"
                    name="email"
                    data-validate="email"
                    required
                >
                <small class="hint"></small>
            </div>
        </div>

        <!-- ======================
             PASSWORD
             ====================== -->
        <div class="form-group">
            <label>Password</label>
            <div class="input-hint">
                <input
                    type="password"
                    name="password"
                    data-validate="password"
                    required
                >
                <small class="hint"></small>
            </div>
        </div>

        <!-- ======================
             USER ONLY
             ====================== -->
        <?php if (!$isMod): ?>
            <div class="form-group">
                <label>Level</label>
                <input type="number" name="level" min="1" value="1" required>
            </div>

            <div class="form-group">
                <label>Green Points</label>
                <input type="number" name="greenPoints" min="0" value="0" required>
            </div>
        <?php endif; ?>

        <!-- ======================
             MOD ONLY
             ====================== -->
        <?php if ($isMod): ?>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phoneNumber" required>
            </div>
        <?php endif; ?>

        <!-- ======================
             ACTIONS
             ====================== -->
        <div class="form-action">
            <button type="submit" class="btn" disabled>
                Create
            </button>

            <a href="?module=user&page=<?= $type ?>" class="btn">
                Cancel
            </a>
        </div>

    </form>
</div>

