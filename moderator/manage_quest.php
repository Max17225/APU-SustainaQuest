<?php
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';
require_once 'mod_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only a logged-in Moderator can access this page
require_role('moderator');

require_once '../includes/header.php';

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quest'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'Daily';
    $pointReward = intval($_POST['pointReward'] ?? 0);
    $expReward = intval($_POST['expReward'] ?? 0);

    if ($title === '' || $description === '') {
        $notice = 'Title and description are required.';
    } else {
        $modId = $_SESSION['user_id'] ?? null;
        if (create_quest($conn, $title, $description, $type, $pointReward, $expReward, $modId)) {
            $notice = 'Quest created successfully.';
        } else {
            $notice = 'Failed to create quest.';
        }
    }
}

$quests = fetch_all_quests($conn);
?>

<div class="container">
    <h1>Manage Quests</h1>

    <?php if ($notice): ?>
        <div class="status-info"><?php echo htmlspecialchars($notice); ?></div>
    <?php endif; ?>

    <section class="create-quest">
        <h2>Create Quest</h2>
        <form method="POST" action="manage_quest.php">
            <label>Title<br>
                <input type="text" name="title" required>
            </label>
            <label>Description<br>
                <textarea name="description" rows="4" required></textarea>
            </label>
            <label>Type<br>
                <select name="type">
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                </select>
            </label>
            <label>Point Reward<br>
                <input type="number" name="pointReward" value="0">
            </label>
            <label>EXP Reward<br>
                <input type="number" name="expReward" value="0">
            </label>
            <button type="submit" name="create_quest">Create Quest</button>
        </form>
    </section>

    <section class="quest-list">
        <h2>Existing Quests</h2>
        <?php if (empty($quests)): ?>
            <p>No quests found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Title</th><th>Type</th><th>Points</th><th>EXP</th><th>Active</th><th>Created</th></tr>
                </thead>
                <tbody>
                <?php foreach ($quests as $q): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($q['title']); ?></td>
                        <td><?php echo htmlspecialchars($q['type']); ?></td>
                        <td><?php echo (int)$q['pointReward']; ?></td>
                        <td><?php echo (int)$q['expReward']; ?></td>
                        <td><?php echo $q['isActive'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars($q['createDate']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>