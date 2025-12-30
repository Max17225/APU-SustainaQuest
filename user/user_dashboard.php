<?php
// 1. SESSION & AUTH
session_start();
require_once '../includes/session_check.php';

// Forces login. If not logged in, they get kicked out.
require_login();

$user_id = $_SESSION['user_id'];

// 2. INCLUDES & SETTINGS
$path = "../";
$page_css = "user_dashboard.css";

require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once 'user_functions.php';

// ================= FETCH DATA (DATABASE LOGIC) =================

// 3. Get User Summary (Name, Level, Points)
$user_stmt = $conn->prepare("
    SELECT userName, level, levelProgress, greenPoints
    FROM users
    WHERE userId = ?
");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// 4. Automate Badge Checking
// (Forces system to check/award badges based on latest stats)
check_and_award_badges($conn, $user_id);

// 5. Level Calculation
$current_xp = (int) $user['levelProgress'];
$required_xp = get_required_xp($user['level']); // From user_functions.php

// Avoid division by zero
if ($required_xp > 0) {
    // Calculate percentage for progress bar (capped at 100%)
    $level_percent = min(100, ($current_xp / $required_xp) * 100);
} else {
    $level_percent = 0;
}

// 6. Get Total Earned Badges Count
$badge_count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM userbadges
    WHERE userId = $user_id
");
$badge_count = $badge_count_result->fetch_assoc()['total'];

// 7. Calculate Global Rank
// Logic: Count how many users have MORE points than the current user + 1
$rank_sql = "
    SELECT COUNT(*) + 1 AS user_rank
    FROM users
    WHERE greenPoints > {$user['greenPoints']}
";
$rank_result = $conn->query($rank_sql);
$user_rank = $rank_result->fetch_assoc()['user_rank'];

// 8. Get Recent Activity Feed
// Logic: Combine (UNION) Redemptions and Completed Quests into one list
$activity_sql = "
    (SELECT CONCAT('You redeemed ', i.itemName, ' for ', i.pointCost, ' pts') AS activity,
            r.redempDate AS activity_date
     FROM redemptions r
     JOIN items i ON r.itemId = i.itemId
     WHERE r.userId = $user_id)

    UNION ALL

    (SELECT CONCAT('Completed ', q.title, ' (+', q.pointReward, ' pts)') AS activity,
            qs.verifyDate AS activity_date
     FROM questsubmissions qs
     JOIN quests q ON qs.questId = q.questId
     WHERE qs.submittedByUserId = $user_id
       AND qs.approveStatus = 'Approved')

    ORDER BY activity_date DESC
    LIMIT 6
";
$activities = $conn->query($activity_sql);

// 9. Get Quest Status (Specific for the Quest Card)
$quest_status = $conn->query("
    SELECT q.title, qs.approveStatus
    FROM questsubmissions qs
    JOIN quests q ON qs.questId = q.questId
    WHERE qs.submittedByUserId = $user_id
    ORDER BY qs.submitDate DESC
    LIMIT 4
");

// 10. Fetch Badge Data (For Grid Display)

// A. Fetch ALL available badges (to display locked state)
$all_badges_result = $conn->query("SELECT badgeId, badgeName, description, badgeIconURL FROM badges ORDER BY badgeId ASC");
$all_badges = [];
if ($all_badges_result) {
    while ($row = $all_badges_result->fetch_assoc()) {
        $all_badges[] = $row;
    }
}

// B. Fetch IDs of badges the user has actually earned
$user_badges_result = $conn->query("SELECT badgeId FROM userbadges WHERE userId = $user_id");
$earned_badge_ids = [];
if ($user_badges_result) {
    while ($row = $user_badges_result->fetch_assoc()) {
        $earned_badge_ids[] = $row['badgeId'];
    }
}
?>

<div class="dashboard-container">

    <div class="dashboard-grid top">

        <div class="dashboard-card">
            <h3>Total Green Points</h3>
            <h2><?= number_format($user['greenPoints']) ?></h2>
            <button class="btn-visit-shop" onclick="location.href='shop.php'">
                Visit Reward Shop
            </button>
        </div>

        <div class="dashboard-card">
            <h3>Level <?= $user['level'] ?></h3>
            <div class="progress-bg">
                <div class="progress-fill" style="width: <?= $level_percent ?>%;"></div>
            </div>
            <small class="level-text">
                <?= $current_xp ?> / <?= $required_xp ?> XP to next level
            </small>
        </div>

        <div class="dashboard-card">
            <h3>Your Rank</h3>
            <h2>#<?= $user_rank ?></h2>
            <small class="level-text">Based on Green Points</small>
        </div>

        <div class="dashboard-card">
            <h3>Badges Earned</h3>
            <h2><?= $badge_count ?></h2>
            <small class="level-text">Collect them all!</small>
        </div>

    </div>

    <div class="dashboard-grid bottom">

        <div class="dashboard-card">
            <h3>Recent Activity</h3>
            <ul class="dashboard-list scrollable-list">
                <?php if ($activities && $activities->num_rows > 0): ?>
                    <?php while ($row = $activities->fetch_assoc()): ?>
                        <li> <?= htmlspecialchars($row['activity']) ?></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="empty">No recent activity.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="dashboard-card">
            <h3>My Quest Status</h3>
            <ul class="dashboard-list scrollable-list">
                <?php 
                // Re-running query here if variable was overwritten or strictly for view context
                $quest_status = $conn->query("
                    SELECT q.title, qs.approveStatus, qs.submitDate
                    FROM questsubmissions qs
                    JOIN quests q ON qs.questId = q.questId
                    WHERE qs.submittedByUserId = $user_id
                    ORDER BY qs.submitDate DESC
                    LIMIT 4
                ");

                if ($quest_status && $quest_status->num_rows > 0): 
                    while ($q = $quest_status->fetch_assoc()): 
                        $formattedDate = date("M j, g:i a", strtotime($q['submitDate']));
                ?>
                        <li>
                            <div class="quest-info">
                                <strong><?= htmlspecialchars($q['title']) ?></strong>
                                <small class="date-text"><?= $formattedDate ?></small>
                            </div>
                            
                            <span class="status-badge
                                <?= strtolower($q['approveStatus']) === 'approved'
                                    ? 'status-completed'
                                    : (strtolower($q['approveStatus']) === 'pending'
                                        ? 'status-pending'
                                        : 'status-rejected') ?>">
                                <?= $q['approveStatus'] ?>
                            </span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="empty">No quests submitted yet.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="dashboard-card">
            <h3>My Badges</h3>
            <div class="badges-container">
                <?php 
                // Loop through ALL badges to create the grid
                foreach ($all_badges as $badge): 
                    $badge_id = $badge['badgeId'];
                    $is_earned = in_array($badge_id, $earned_badge_ids);
                    
                    // Prepare Data for Modal
                    $b_status = $is_earned ? 'earned' : 'locked';
                    $b_name   = htmlspecialchars($badge['badgeName']);
                    $b_desc   = htmlspecialchars($badge['description']);
                    $b_img_url = !empty($badge['badgeIconURL']) ? $path . $badge['badgeIconURL'] : "";
                ?>
                    <div class="badge-icon-slot <?= $is_earned ? '' : 'empty-slot' ?>" 
                         data-status="<?= $b_status ?>"
                         data-name="<?= $b_name ?>"
                         data-desc="<?= $b_desc ?>"
                         data-img="<?= $b_img_url ?>">
                        
                        <?php if ($b_img_url): ?>
                            <img src="<?= $b_img_url ?>" 
                                 alt="<?= $b_name ?>" 
                                 class="<?= $is_earned ? '' : 'locked-grayscale' ?>">
                        <?php else: ?>
                            <span class="locked-icon">🔒</span>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>

                <?php for ($i = count($all_badges); $i < 10; $i++): ?>
                    <div class="badge-icon-slot empty-slot" 
                         data-status="locked" 
                         data-name="Coming Soon" 
                         data-desc="This badge has not been revealed yet.">
                        <span class="locked-icon">🔒</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

    </div>

</div>

<div id="badgeModal" class="badge-modal">
  <div class="badge-modal-content">
    <span class="close-modal">&times;</span>
    
    <div class="modal-header-section">
        <img id="modalBadgeImg" src="" alt="Badge Icon" style="display:none;">
        <span id="modalLockedIcon" class="locked-icon" style="font-size: 4rem; display:none;">🔒</span>
    </div>

    <h2 id="modalBadgeName">Badge Name</h2>
    <span id="modalBadgeStatus" class="status-pill"></span>
    
    <div class="modal-body-section">
        <p id="modalBadgeDesc">Description goes here...</p>
    </div>
  </div>
</div>

<script>
// ==========================================
// 1. SETUP DOM ELEMENTS
// ==========================================
const modal = document.getElementById("badgeModal");
const closeBtn = document.getElementsByClassName("close-modal")[0];
const badgeSlots = document.querySelectorAll('.badge-icon-slot');

// Modal Inner Elements
const mImg = document.getElementById("modalBadgeImg");
const mLockedIcon = document.getElementById("modalLockedIcon");
const mName = document.getElementById("modalBadgeName");
const mDesc = document.getElementById("modalBadgeDesc");
const mStatus = document.getElementById("modalBadgeStatus");

// ==========================================
// 2. MODAL LOGIC
// ==========================================

// Attach Click Event to every Badge Slot
badgeSlots.forEach(slot => {
    slot.addEventListener('click', function() {
        // 1. Read data attributes from clicked slot
        const data = this.dataset;

        // 2. Populate Modal Text
        mName.textContent = data.name;
        mDesc.textContent = data.desc;
        mStatus.textContent = data.status === 'earned' ? 'Earned' : 'Locked';
        
        // 3. Update Status Pill Style
        mStatus.className = 'status-pill ' + data.status;

        // 4. Handle Image vs Locked Icon
        if (data.img) {
            mImg.src = data.img;
            mImg.style.display = 'inline-block';
            mLockedIcon.style.display = 'none';

            // VISUAL LOGIC: If status is locked, grey out the popup image too
            if (data.status === 'locked') {
                mImg.style.filter = "grayscale(100%) opacity(0.5)";
            } else {
                mImg.style.filter = "none";
            }
        } else {
            // No image found, default to generic lock
            mImg.style.display = 'none';
            mLockedIcon.style.display = 'inline-block';
        }

        // 5. Show Modal
        modal.style.display = "flex";
    });
});

// ==========================================
// 3. CLOSE HANDLERS
// ==========================================

// Close on 'X' click
closeBtn.onclick = function() { 
    modal.style.display = "none"; 
}

// Close on outside click
window.onclick = function(event) {
    if (event.target == modal) { 
        modal.style.display = "none"; 
    }
}
</script>

<?php include '../includes/footer.php'; ?>