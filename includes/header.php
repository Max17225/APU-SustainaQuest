<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login status
$is_logged_in = isset($_SESSION['user_id']);
$path = $path ?? "./"; 

// Get current page name to set active class 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustainaQuest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">

    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?php echo $path; ?>assets/css/<?php echo $page_css; ?>">
    <?php endif; ?>

    <style>
        /* Active Link Style (Permanent Underline) */
        .nav-link.active {
            position: relative;
            color: #fff; 
            font-weight: 600;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #fff; 
        }

        /* Profile Header Styles */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
            cursor: pointer;
        }

        .user-profile .user-name {
            font-size: 1rem;
            font-weight: 500;
        }

        .profile-pic-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #d9d9d9; 
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-pic-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <header class="main-header">
        
        <div class="menu-toggle" onclick="toggle_sidebar()">
            <img src="<?php echo $path; ?>assets/image/sidebar.png" alt="Menu" style="width: 30px; height: 30px; cursor: pointer;">
        </div>

        <div class="logo-container">
            <img src="<?php echo $path; ?>assets/image/SustainaQuest Logo.png" alt="SustainaQuest Logo" class="logo-img" style="width: 50px; height: 50px; object-fit: contain;"> 
            <a href="<?php echo $path; ?>index.php" style="text-decoration:none; color:white;">
                <span class="site-title">SustainaQuest</span>
            </a>
        </div>

        <nav class="main-nav">
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $path; ?>user/user_dashboard.php" class="nav-link <?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            <?php else: ?>
                <a href="<?php echo $path; ?>index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
            <?php endif; ?>

            <a href="<?php echo $path; ?>user/quests.php" class="nav-link <?php echo ($current_page == 'quests.php') ? 'active' : ''; ?>">Quests</a>
            <a href="<?php echo $path; ?>leaderboard.php" class="nav-link <?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">Leaderboard</a>
            <a href="<?php echo $path; ?>shop.php" class="nav-link <?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">Reward Shop</a>
        </nav>

        <div class="auth-action">
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $path; ?>user/profile.php" class="user-profile">
                    <span class="user-name">User</span> <div class="profile-pic-container">
                        <?php if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
                            <img src="<?php echo $path . $_SESSION['profile_pic']; ?>" alt="Profile">
                        <?php endif; ?>
                    </div>
                </a>
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
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $path; ?>user/dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            <?php else: ?>
                <a href="<?php echo $path; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
            <?php endif; ?>
            
            <a href="<?php echo $path; ?>user/quests.php" class="<?php echo ($current_page == 'quests.php') ? 'active' : ''; ?>">Quests</a>
            <a href="<?php echo $path; ?>leaderboard.php" class="<?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">Leaderboard</a>
            <a href="<?php echo $path; ?>shop.php" class="<?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">Reward Shop</a>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 5px 20px;">
            
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $path; ?>user/profile.php">My Profile</a>
                <a href="<?php echo $path; ?>logout.php" style="color: #ff6b6b;">Logout</a>
            <?php else: ?>
                <a href="<?php echo $path; ?>login.php" class="sidebar-btn-login">Login / Register</a>
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