<?php
/* =======================
   1. SESSION & AUTH
======================= */
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth_page/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

/* =======================
   2. INCLUDES
======================= */
$path = "../";
$page_css = "user_dashboard.css";

require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once 'user_functions.php'; 

/* =======================
   3. USER SUMMARY
======================= */
$user_stmt = $conn->prepare("
    SELECT userName, level, levelProgress, greenPoints
    FROM users
    WHERE userId = ?
");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// ============================================================
// Forces the system to check your badges every time you look at your dashboard.
// ============================================================
check_and_award_badges($conn, $user_id);

/* =======================
   4. LEVEL CALCULATION
======================= */
$current_xp = (int) $user['levelProgress'];
// Use function from user_functions.php
$required_xp = get_required_xp($user['level']);

if ($required_xp > 0) {
    $level_percent = min(100, ($current_xp / $required_xp) * 100);
} else {
    $level_percent = 0;
}

/* =======================
   5. BADGE COUNT
======================= */
$badge_count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM userbadges
    WHERE userId = $user_id
");
$badge_count = $badge_count_result->fetch_assoc()['total'];

/* =======================
   6. RANK CALCULATION
======================= */
$rank_sql = "
    SELECT COUNT(*) + 1 AS user_rank
    FROM users
    WHERE greenPoints > {$user['greenPoints']}
";
$rank_result = $conn->query($rank_sql);
$user_rank = $rank_result->fetch_assoc()['user_rank'];

/* =======================
   7. RECENT ACTIVITY
======================= */
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

/* =======================
   8. QUEST STATUS
======================= */
$quest_status = $conn->query("
    SELECT q.title, qs.approveStatus
    FROM questsubmissions qs
    JOIN quests q ON qs.questId = q.questId
    WHERE qs.submittedByUserId = $user_id
    ORDER BY qs.submitDate DESC
    LIMIT 4
");

/* =======================
   9. USER BADGES 
======================= */
// A. Fetch ALL badges (so can show locked ones)
$all_badges_result = $conn->query("SELECT badgeId, badgeName, description, badgeIconURL FROM badges ORDER BY badgeId ASC");
$all_badges = [];
if ($all_badges_result) {
    while ($row = $all_badges_result->fetch_assoc()) {
        $all_badges[] = $row;
    }
}

// B. Fetch User's Earned Badge IDs
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
                        <li>🌱 <?= htmlspecialchars($row['activity']) ?></li>
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
                // Loop through ALL badges
                foreach ($all_badges as $badge): 
                    $badge_id = $badge['badgeId'];
                    $is_earned = in_array($badge_id, $earned_badge_ids);
                    
                    // Prepare Popup Data
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
// Get DOM Elements
const modal = document.getElementById("badgeModal");
const closeBtn = document.getElementsByClassName("close-modal")[0];
const badgeSlots = document.querySelectorAll('.badge-icon-slot');

// Modal Content Elements
const mImg = document.getElementById("modalBadgeImg");
const mLockedIcon = document.getElementById("modalLockedIcon");
const mName = document.getElementById("modalBadgeName");
const mDesc = document.getElementById("modalBadgeDesc");
const mStatus = document.getElementById("modalBadgeStatus");

// Function to open modal and fill data
badgeSlots.forEach(slot => {
    slot.addEventListener('click', function() {
        // 1. Read data from clicked slot
        const data = this.dataset;

        // 2. Populate Modal Text
        mName.textContent = data.name;
        mDesc.textContent = data.desc;
        mStatus.textContent = data.status === 'earned' ? 'Earned' : 'Locked';
        
        // 3. Update Status Pill Class
        mStatus.className = 'status-pill ' + data.status;

        // 4. Show correct icon (Image vs Lock emoji)
        if (data.img) {
            mImg.src = data.img;
            mImg.style.display = 'inline-block';
            mLockedIcon.style.display = 'none';

            // UPDATED: If status is locked, make the popup image grey too!
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

// Close Modal Logic
closeBtn.onclick = function() { modal.style.display = "none"; }
window.onclick = function(event) {
    if (event.target == modal) { modal.style.display = "none"; }
}
</script>

<?php include '../includes/footer.php'; ?>