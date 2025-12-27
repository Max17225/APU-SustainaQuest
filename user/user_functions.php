<?php
/* ============================================================
   SECTION A: LEVELING LOGIC (Exponential Curve)
   ============================================================ */

function get_required_xp($level) {
    $base_xp = 100;
    $exponent = 1.5; 
    return floor($base_xp * pow($level, $exponent));
}

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

    // 3. Loop to handle multiple level ups
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
   ============================================================ */

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

    // =================================================
    // 2. DEFINE MILESTONES 
    // =================================================
    
    // A. Green Points (3 Badges)
    $point_milestones = [
        500   => 'Earth Defender',
        1000  => 'Eco Legend',
        5000  => 'Sustainability God'
    ];

    // B. Levels (3 Badges)
    $level_milestones = [
        2  => 'Level Up',
        5 => 'High Flyer',
        10 => 'Top Tier'
    ];

    // C. Quest Counts (3 Badges)
    $quest_milestones = [
        1  => 'First Step',
        5  => 'Quest Hunter',
        20 => 'Quest Master'
    ];

    // 3. CHECK & AWARD LOGIC
    $award_func = function($badge_name) use ($conn, $user_id) {
        
        $check = $conn->prepare("
            SELECT 1 
            FROM userbadges ub 
            JOIN badges b ON ub.badgeId = b.badgeId 
            WHERE ub.userId = ? AND b.badgeName = ?
        ");
        $check->bind_param("is", $user_id, $badge_name);
        $check->execute();
        
        if ($check->get_result()->num_rows == 0) {
            // Get Badge ID
            $id_check = $conn->prepare("SELECT badgeId FROM badges WHERE badgeName = ?");
            $id_check->bind_param("s", $badge_name);
            $id_check->execute();
            $b_row = $id_check->get_result()->fetch_assoc();
            
            if ($b_row) {
                // Award it
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
?>