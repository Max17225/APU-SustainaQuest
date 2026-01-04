<!-- admin/setting/main_section/setting_optin.php -->

<?php
$success = $_GET['success'] ?? null;
$error   = $_GET['error'] ?? null;
?>

<div class="setting-panel">
        <!-- Change Password -->
    <section class="settings-card">
        <h2>Change Password</h2>

        <form action="setting/process/update_password.php" method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="8">
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-primary">Update Password</button>
        </form>

        <?php if ($success): ?>
            <div class="alert success">
                Password updated successfully
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error">
                <?php
                    if ($error === 'wrong_password') echo 'Current password is incorrect.';
                    if ($error === 'password_mismatch') echo 'Passwords do not match.';
                ?>  
            </div>
        <?php endif; ?>

    </section>

    <!-- Theme -->
    <section class="settings-card">
        <h2>Theme</h2>

        <form action="setting/process/change_theme.php" method="POST">
            <div class="theme-toggle">
                <label>
                    <input type="radio" name="theme" value="Dark" required>
                    Dark
                </label>

                <label>
                    <input type="radio" name="theme" value="Fridge">
                    Fridge
                </label>
            </div>

            <button type="submit" class="btn-primary">Apply Theme</button>
        </form>
    </section>
</div>