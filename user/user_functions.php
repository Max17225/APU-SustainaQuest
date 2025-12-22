<?php
/* ============================================================
   user_functions.php
   ------------------------------------------------------------
   Centralized logic for User Progression, Badges, and Quest Data.
   Include this file in any page that needs to access user stats
   or quest information.
   ============================================================ */

/* ============================================================
   SECTION A: LEVELING LOGIC
   Handles XP calculations and Level Up events.
   ============================================================ */

/**
 * Calculates the total XP required to reach the next level.
 * Formula: Base(100) * Level ^ 1.5
 * * Used in: 
 * - user/user_dashboard.php (To calculate progress bar %)
 * - Internal: add_xp_and_level_up()
 */
function get_required_xp($level) {
    $base_xp = 100;
    $exponent = 1.5; 
    return floor($base_xp * pow($level, $exponent));
}

/**
 * Adds XP to a user, checks if they leveled up, and updates the DB.
 * If a level up occurs, it automatically triggers the Badge Check.
 * * Used in:
 * - user/submit_quest.php (When a quest is instantly AI-approved)
 * - admin/approve_quest.php (When an admin manually approves)
 */
function add_xp_and_level_up($conn, $user_id, $xp_to_add) {
    
    // 1. Get current stats
    $stmt = $conn->prepare("SELECT level, levelProgress FROM users WHERE userId = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) return;

    $current_lvl = (int)$user['level'];
    $current_xp  = (int)$user['levelProgress'];
    
    // 2. Add the new XP
    $current_xp += $xp_to_add;
    $leveled_up = false;

    // 3. Loop to handle multiple level ups (e.g., big XP reward)
    while (true) {
        $required = get_required_xp($current_lvl);
        
        if ($current_xp >= $required) {
            $current_xp -= $required; 
            $current_lvl++;           
            $leveled_up = true;
        } else {
            break; 
        }
    }

    // 4. Update Database
    $update = $conn->prepare("UPDATE users SET level = ?, levelProgress = ? WHERE userId = ?");
    $update->bind_param("idi", $current_lvl, $current_xp, $user_id);
    $update->execute();

    // 5. If leveled up, trigger Badge Check
    if ($leveled_up) {
        check_and_award_badges($conn, $user_id);
    }
}

/* ============================================================
   SECTION B: BADGE LOGIC
   Handles checking requirements and awarding badges.
   ============================================================ */

/**
 * Scans user statistics (Points, Level, Quest Count) against
 * defined milestones. If a milestone is met, the badge is awarded.
 * * Used in:
 * - user/user_dashboard.php (Runs on page load to sync badges)
 * - Internal: add_xp_and_level_up() (Runs immediately after leveling)
 */
function check_and_award_badges($conn, $user_id) {
    
    // 1. GATHER USER DATA
    $u_stmt = $conn->prepare("SELECT greenPoints, level FROM users WHERE userId = ?");
    $u_stmt->bind_param("i", $user_id);
    $u_stmt->execute();
    $user_data = $u_stmt->get_result()->fetch_assoc();
    
    if (!$user_data) return; 
    
    $current_points = $user_data['greenPoints'];
    $current_level  = $user_data['level'];

    // Get Quest Count (Approved only)
    $q_stmt = $conn->prepare("SELECT COUNT(*) as total FROM questsubmissions WHERE submittedByUserId = ? AND approveStatus = 'Approved'");
    $q_stmt->bind_param("i", $user_id);
    $q_stmt->execute();
    $quest_count = $q_stmt->get_result()->fetch_assoc()['total'];

    // 2. DEFINE MILESTONES 
    
    // A. Green Points
    $point_milestones = [
        500   => 'Earth Defender',
        1000  => 'Eco Legend',
        5000  => 'Sustainability God'
    ];

    // B. Levels
    $level_milestones = [
        2  => 'Level Up',
        5  => 'High Flyer',
        10 => 'Top Tier'
    ];

    // C. Quest Counts
    $quest_milestones = [
        1  => 'First Step',
        5  => 'Quest Hunter',
        20 => 'Quest Master'
    ];

    // 3. CHECK & AWARD LOGIC
    $award_func = function($badge_name) use ($conn, $user_id) {
        
        // Check if user already owns this badge
        $check = $conn->prepare("
            SELECT 1 
            FROM userbadges ub 
            JOIN badges b ON ub.badgeId = b.badgeId 
            WHERE ub.userId = ? AND b.badgeName = ?
        ");
        $check->bind_param("is", $user_id, $badge_name);
        $check->execute();
        
        // If not owned, award it
        if ($check->get_result()->num_rows == 0) {
            $id_check = $conn->prepare("SELECT badgeId FROM badges WHERE badgeName = ?");
            $id_check->bind_param("s", $badge_name);
            $id_check->execute();
            $b_row = $id_check->get_result()->fetch_assoc();
            
            if ($b_row) {
                $ins = $conn->prepare("INSERT INTO userbadges (userId, badgeId) VALUES (?, ?)");
                $ins->bind_param("ii", $user_id, $b_row['badgeId']);
                $ins->execute();
            }
        }
    };

    // Run the checks
    foreach ($point_milestones as $pts => $name) {
        if ($current_points >= $pts) $award_func($name);
    }

    foreach ($level_milestones as $lvl => $name) {
        if ($current_level >= $lvl) $award_func($name);
    }

    foreach ($quest_milestones as $qty => $name) {
        if ($quest_count >= $qty) $award_func($name);
    }
}

/* ============================================================
   SECTION C: QUEST DATA FETCHING
   Handles retrieving active quests, cooldowns, and history.
   ============================================================ */

/**
 * Fetches all quests marked as 'Active' from the database.
 * Separates them into 'Daily' and 'Weekly' arrays.
 * Limits the output (5 Daily, 3 Weekly).
 * * Used in:
 * - user/quests.php (To display the main grid)
 */
function get_available_quests($conn) {
    $daily = [];
    $weekly = [];

    $sql = "SELECT * FROM quests WHERE isActive = 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if ($row['type'] === 'Daily') {
                $daily[] = $row;
            } else if ($row['type'] === 'Weekly') {
                $weekly[] = $row;
            }
        }
    }
    
    // Slice arrays (Business Logic: Limit to 5 daily, 3 weekly)
    return [
        'daily' => array_slice($daily, 0, 5),
        'weekly' => array_slice($weekly, 0, 3)
    ];
}

/**
 * Returns an array of Quest IDs that the user CANNOT currently do.
 * - Daily Quests: Locked if submitted in the last 24 hours.
 * - Weekly Quests: Locked if submitted in the current calendar week.
 * * Used in:
 * - user/quests.php (To gray out/disable buttons)
 */
function get_locked_quest_ids($conn, $uid) {
    $locked_ids = [];
    
    $sql = "SELECT q.questId 
            FROM questsubmissions qs
            JOIN quests q ON qs.questId = q.questId
            WHERE qs.submittedByUserId = ? 
            AND qs.approveStatus != 'Rejected'
            AND (
                (q.type = 'Daily' AND qs.submitDate >= (NOW() - INTERVAL 1 DAY))
                OR
                (q.type = 'Weekly' AND YEARWEEK(qs.submitDate, 1) = YEARWEEK(CURDATE(), 1))
            )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $locked_ids[] = (string)$row['questId'];
    }
    return $locked_ids;
}

/**
 * Fetches the complete submission history for a user.
 * Returns an associative array categorized by status: 
 * ['pending', 'completed', 'rejected'].
 * * Used in:
 * - user/quests.php (To display history tab)
 * - user/user_dashboard.php (To display recent activity list)
 */
function get_user_quest_history($conn, $uid) {
    $history = [
        'pending' => [],
        'completed' => [],
        'rejected' => []
    ];

    $sql = "SELECT qs.*, q.title, q.type, q.questIconURL, q.pointReward, q.expReward, q.description, m.modName 
            FROM questsubmissions qs 
            JOIN quests q ON qs.questId = q.questId 
            LEFT JOIN moderators m ON qs.verifiedByModeratorId = m.moderatorId
            WHERE qs.submittedByUserId = ? 
            ORDER BY qs.submitDate DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        if ($row['approveStatus'] == 'Pending') {
            $history['pending'][] = $row;
        } elseif ($row['approveStatus'] == 'Approved') {
            $history['completed'][] = $row;
        } else {
            $history['rejected'][] = $row;
        }
    }
    return $history;
}

/* ============================================================
   SECTION D: LEADERBOARD LOGIC
   Handles fetching and sorting user rankings.
   ============================================================ */

function get_leaderboard_data($conn, $filter = 'points', $limit = 50, $offset = 0) {
    $leaderboard = [];
    
    // 1. Determine sorting logic based on filter
    // CRITICAL FIX: We must use the full subquery string in ORDER BY, not the alias, 
    // because Window Functions (RANK) run before Aliases are created.
    switch ($filter) {
        case 'level':
            $main_stat_field = "u.level"; 
            $order_clause = "u.level DESC, u.levelProgress DESC";
            break;

        case 'quests':
            $main_stat_field = "totalQuests";
            // The fix: Copy the subquery logic here
            $quest_subquery = "(SELECT COUNT(*) FROM questsubmissions qs WHERE qs.submittedByUserId = u.userId AND qs.approveStatus = 'Approved')";
            $order_clause = "$quest_subquery DESC, u.greenPoints DESC";
            break;

        case 'points':
        default:
            $filter = 'points'; 
            $main_stat_field = "u.greenPoints";
            $order_clause = "u.greenPoints DESC, u.level DESC";
            break;
    }

    // 2. Build the Query
    $sql = "
        SELECT 
            u.userId, 
            u.userName, 
            u.greenPoints, 
            u.level,
            -- Subquery to count approved quests (Aliased for display)
            (SELECT COUNT(*) FROM questsubmissions qs WHERE qs.submittedByUserId = u.userId AND qs.approveStatus = 'Approved') as totalQuests,
            -- Window function using the RAW sorting logic
            RANK() OVER (ORDER BY $order_clause) as current_rank
        FROM users u
        ORDER BY $order_clause
        LIMIT ? OFFSET ?
    ";

    // 3. Execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // 4. Fetch results
    while ($row = $result->fetch_assoc()) {
        // Determine the 'main stat' value dynamically based on what we are filtering
        if ($filter == 'level') {
            $row['main_stat_value'] = $row['level'];
        } elseif ($filter == 'quests') {
            $row['main_stat_value'] = $row['totalQuests'];
        } else {
            $row['main_stat_value'] = $row['greenPoints'];
        }
        $leaderboard[] = $row;
    }

    return $leaderboard;
}
?>