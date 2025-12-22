<?php 
// 1. SESSION & INCLUDES
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$path = "../";
$page_css = "leaderboard.css"; 

include '../includes/db_connect.php';
include '../includes/header.php';
include 'user_functions.php';

// ================= LEADERBOARD LOGIC =================

// 1. Determine Current Filter
$valid_filters = ['points', 'level', 'quests'];
$current_filter = isset($_GET['filter']) && in_array($_GET['filter'], $valid_filters) ? $_GET['filter'] : 'points';

// 2. Define labels
$stat_label = "";
switch ($current_filter) {
    case 'level': $stat_label = "Highest Level"; break;
    case 'quests': $stat_label = "Quests Completed"; break; 
    case 'points': default: $stat_label = "Green Points"; break;
}

// 3. Fetch Data
$top3_data = get_leaderboard_data($conn, $current_filter, 3, 0);
$global_data = get_leaderboard_data($conn, $current_filter, 50, 3);

// Helper to get trophy images
function get_rank_image($path, $rank) {
    $r = intval($rank);
    // UPDATED PATH: assets/image/trophies/Trophy_X.png
    if ($r >= 1 && $r <= 3) {
        return $path . "assets/image/trophies/Trophy_" . $r . ".png";
    }
    // Default fallback
    return $path . 'assets/image/user-avatar.png';
}
?>

<div class="leaderboard-container">

    <div class="leaderboard-grid">
        
        <div class="top-three-wrapper">
            <h2 class="lb-section-title">Top Leaders</h2>
            
            <div class="top-3-pyramid">
                <?php 
                // Pad array to ensure exactly 3 slots exist with NULL if no user
                $padded_top3 = array_pad($top3_data, 3, null);
                
                // Visual Order: Rank 2 (Left), Rank 1 (Center), Rank 3 (Right)
                $display_indices = [1, 0, 2]; 

                foreach($display_indices as $idx):
                    $user = $padded_top3[$idx];
                    // Visual rank is based on index position (0=Rank1, 1=Rank2...)
                    $visual_rank = $idx + 1; 
                    
                    // Specific class for styling
                    $rank_class = "rank-" . $visual_rank;
                    
                    // Always get the trophy image for this rank position
                    $img_src = get_rank_image($path, $visual_rank);

                    if ($user): 
                        // --- REAL USER FOUND ---
                ?>
                    <div class="lb-card <?= $rank_class ?>">
                        <span class="lb-rank-badge">#<?= $user['current_rank'] ?></span>
                        
                        <div class="lb-img-box">
                            <img src="<?= $img_src ?>" alt="Rank <?= $visual_rank ?>">
                        </div>
                        
                        <div class="lb-name"><?= htmlspecialchars($user['userName']) ?></div>
                        <div class="lb-stat"><?= number_format($user['main_stat_value']) ?></div>
                        <div class="lb-stat-label"><?= $stat_label ?></div>
                    </div>

                <?php else: 
                        // --- NO USER (VACANT SLOT) ---
                ?>
                    <div class="lb-card <?= $rank_class ?> empty-slot">
                        <span class="lb-rank-badge">#<?= $visual_rank ?></span>
                        <div class="lb-img-box" style="opacity: 0.4;">
                            <img src="<?= $img_src ?>" alt="Rank <?= $visual_rank ?>">
                        </div>
                        <div class="lb-stat-label" style="margin-top: 10px;">Vacant</div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </div>


        <div class="global-rank-wrapper">
            <h2 class="lb-section-title" style="text-align:left;">Global Rankings</h2>

            <div class="filter-group">
                <span class="filter-label">Filter By:</span>
                <a href="?filter=points" class="filter-btn <?= $current_filter == 'points' ? 'active' : '' ?>">Green Points</a>
                <a href="?filter=level" class="filter-btn <?= $current_filter == 'level' ? 'active' : '' ?>">Highest Level</a>
                <a href="?filter=quests" class="filter-btn <?= $current_filter == 'quests' ? 'active' : '' ?>">Quests Completed</a>
            </div>

            <div class="table-responsive">
                <table class="lb-table">
                    <thead>
                        <tr>
                            <th class="col-rank">#</th>
                            <th>Player</th>
                            <th class="col-stat"><?= $stat_label ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($global_data)): ?>
                            <?php foreach ($global_data as $user): 
                                $rank = $user['current_rank'];
                            ?>
                            <tr>
                                <td class="col-rank"><?= $rank ?></td>
                                <td>
                                    <div class="player-cell">
                                        <span class="table-name"><?= htmlspecialchars($user['userName']) ?></span>
                                    </div>
                                </td>
                                <td class="col-stat"><?= number_format($user['main_stat_value']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align:center; padding: 30px; color:#999;">
                                    No other players found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>