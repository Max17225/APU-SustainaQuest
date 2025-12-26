<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check login status
$is_logged_in = isset($_SESSION['user_id']);

// 2. Set Path Default
$path = $path ?? "./"; 

// 3. Define Home Link
$user_role = $_SESSION['role'] ?? 'guest';

if ($is_logged_in) {
    if ($user_role === 'moderator') {
        $home_link = $path . "moderator/mod_dashboard.php";
    } elseif ($user_role === 'admin') {
        $home_link = $path . "admin/admin_dashboard.php";
    } else {
        $home_link = $path . "user/user_dashboard.php";
    }
} else {
    $home_link = $path . "index.php";
}

// 4. Get Current Page
$current_page = basename($_SERVER['PHP_SELF']);

// ============================================================
// 5. FETCH USERNAME 
// ============================================================
$display_name = "User"; 

if ($is_logged_in) {
    if (!isset($conn)) {
        require_once($path . "includes/db_connect.php");
    }

    // Fetch the name from the database
    $h_stmt = $conn->prepare("SELECT userName FROM users WHERE userId = ?");
    $h_stmt->bind_param("i", $_SESSION['user_id']);
    $h_stmt->execute();
    $h_result = $h_stmt->get_result();
    
    if ($row = $h_result->fetch_assoc()) {
        $display_name = $row['userName'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustainaQuest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">

    <?php if (isset($page_css) && !empty($page_css)): ?>
        <link rel="stylesheet" href="<?php echo $path; ?>assets/css/<?php echo $page_css; ?>">
    <?php endif; ?>
</head>
<body>

    <header class="main-header">
        
        <div class="menu-toggle" onclick="toggle_sidebar()">
            <img src="<?php echo $path; ?>assets/image/sidebar.png" alt="Menu" style="width: 30px; height: 30px; cursor: pointer;">
        </div>

        <div class="logo-container">
            <a href="<?php echo $home_link; ?>" style="text-decoration:none; display:flex; align-items:center; gap:15px;">
                <img src="<?php echo $path; ?>assets/image/SustainaQuest Logo.png" alt="SustainaQuest Logo" class="logo-img"> 
                <span class="site-title" style="color:white;">SustainaQuest</span>
            </a>
        </div>

        <nav class="main-nav">
            <?php if ($user_role === 'moderator'): ?>
                <a href="<?php echo $path; ?>moderator/mod_dashboard.php" class="nav-link <?php echo ($current_page == 'mod_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
                <a href="<?php echo $path; ?>moderator/verify_submissions.php" class="nav-link <?php echo ($current_page == 'verify_submissions.php') ? 'active' : ''; ?>">Verify Submissions</a>
                <a href="<?php echo $path; ?>moderator/manage_quest.php" class="nav-link <?php echo ($current_page == 'manage_quest.php') ? 'active' : ''; ?>">Manage Quests</a>
            
            <?php elseif ($user_role === 'admin'): ?>
                <a href="<?php echo $path; ?>admin/admin_dashboard.php" class="nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            
            <?php else: ?>
                <?php if ($is_logged_in): ?>
                    <a href="<?php echo $path; ?>user/user_dashboard.php" class="nav-link <?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo $path; ?>index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
                <?php endif; ?>

                <a href="<?php echo $path; ?>user/quests.php" class="nav-link <?php echo ($current_page == 'quests.php') ? 'active' : ''; ?>">Quests</a>
                <a href="<?php echo $path; ?>user/leaderboard.php" class="nav-link <?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">Leaderboard</a>
                <a href="<?php echo $path; ?>user/shop.php" class="nav-link <?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">Reward Shop</a>
            <?php endif; ?>
        </nav>

        <div class="auth-action">
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $path; ?>user/profile.php" class="user-profile">
                    
                    <span class="user-name"><?php echo htmlspecialchars($display_name); ?></span> 
                    
                    <div class="profile-pic-container">
                        <?php if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
                            <img src="<?php echo $path . $_SESSION['profile_pic']; ?>" alt="Profile">
                        <?php else: ?>
                             <span style="color:#555; font-size:1.2rem;">👤</span>
                        <?php endif; ?>
                    </div>
                </a>
                <a href="<?php echo $path; ?>includes/logout.php" style="margin-left: 15px; color: #ff6b6b; text-decoration: none; font-weight: 600; font-size: 0.9rem;">Logout</a>
            <?php else: ?>
                <a href="<?php echo $path; ?>auth_page/login.php" class="btn-login">Login/Register</a>
            <?php endif; ?>
        </div>
    </header>

    <div id="mobile-sidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="toggle_sidebar()">&times;</a>
        
        <div class="sidebar-logo-container">
            <img src="<?php echo $path; ?>assets/image/SustainaQuest Logo.png" alt="Sidebar Logo">
        </div>

        <div class="sidebar-content">
            <?php if ($user_role === 'moderator'): ?>
                <a href="<?php echo $path; ?>moderator/mod_dashboard.php" class="<?php echo ($current_page == 'mod_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
                <a href="<?php echo $path; ?>moderator/verify_submissions.php" class="<?php echo ($current_page == 'verify_submissions.php') ? 'active' : ''; ?>">Verify Submissions</a>
                <a href="<?php echo $path; ?>moderator/manage_quest.php" class="<?php echo ($current_page == 'manage_quest.php') ? 'active' : ''; ?>">Manage Quests</a>
            
            <?php elseif ($user_role === 'admin'): ?>
                <a href="<?php echo $path; ?>admin/admin_dashboard.php" class="<?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            
            <?php else: ?>
                <?php if ($is_logged_in): ?>
                    <a href="<?php echo $path; ?>user/user_dashboard.php" class="<?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo $path; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
                <?php endif; ?>
                
                <a href="<?php echo $path; ?>user/quests.php" class="<?php echo ($current_page == 'quests.php') ? 'active' : ''; ?>">Quests</a>
                <a href="<?php echo $path; ?>user/leaderboard.php" class="<?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">Leaderboard</a>
                <a href="<?php echo $path; ?>user/shop.php" class="<?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">Reward Shop</a>
            <?php endif; ?>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 5px 20px;">
            
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $path; ?>user/profile.php">My Profile</a>
                <a href="<?php echo $path; ?>includes/logout.php" style="color: #ff6b6b;">Logout</a>
            <?php else: ?>
                <a href="<?php echo $path; ?>auth_page/login.php" class="sidebar-btn-login">Login / Register</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggle_sidebar() {
            var sidebar = document.getElementById("mobile-sidebar");
            if (sidebar.style.width === "250px") {
                sidebar.style.width = "0";
            } else {
                sidebar.style.width = "250px";
            }
        }
    </script>