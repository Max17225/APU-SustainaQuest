<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check login status
$is_logged_in = isset($_SESSION['user_id']);

// 2. Set Path Default
$path = $path ?? "./"; 

// 3. Define Home Link
$home_link = $is_logged_in ? $path . "user/user_dashboard.php" : $path . "index.php";

// 4. Get Current Page
$current_page = basename($_SERVER['PHP_SELF']);

// ============================================================
// 5. FETCH USERNAME 
// ============================================================
$display_name = "User"; 

if ($is_logged_in) {
    if (!isset($conn)) {
        // Adjust this path if your db_connect is elsewhere
        if(file_exists($path . "includes/db_connect.php")) {
            require_once($path . "includes/db_connect.php");
        }
    }

    // Only run query if connection exists
    if(isset($conn)) {
        $h_stmt = $conn->prepare("SELECT userName FROM users WHERE userId = ?");
        $h_stmt->bind_param("i", $_SESSION['user_id']);
        $h_stmt->execute();
        $h_result = $h_stmt->get_result();
        
        if ($row = $h_result->fetch_assoc()) {
            $display_name = $row['userName'];
        }
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

    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?php echo $path; ?>assets/css/<?php echo $page_css; ?>">
    <?php endif; ?>

    <style>
        .user-profile-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .profile-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #ffffff;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
        }

        .profile-dropdown-menu a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .profile-dropdown-menu a:hover {
            background-color: #f1f1f1;
            color: #28a745; 
        }

        .profile-dropdown-menu a.logout-link:hover {
            background-color: #ffeaea;
            color: #dc3545;
        }

        .show-dropdown {
            display: block;
        }
    </style>
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
            <?php if ($is_logged_in): ?>
                <a href="<?php echo $path; ?>user/user_dashboard.php" class="nav-link <?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            <?php else: ?>
                <a href="<?php echo $path; ?>index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
            <?php endif; ?>

            <a href="<?php echo $path; ?>user/quests.php" class="nav-link <?php echo ($current_page == 'quests.php') ? 'active' : ''; ?>">Quests</a>
            <a href="<?php echo $path; ?>user/leaderboard.php" class="nav-link <?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">Leaderboard</a>
            <a href="<?php echo $path; ?>user/shop.php" class="nav-link <?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">Reward Shop</a>
        </nav>

        <div class="auth-action">
            <?php if ($is_logged_in): ?>
                
                <div class="user-profile-container" onclick="toggleProfileDropdown(event)">
                    
                    <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
                        <span class="user-name"><?php echo htmlspecialchars($display_name); ?></span> 
                        
                        <div class="profile-pic-container">
                            <?php 
                            $default_pic = $path . "assets/image/profile_picture.png";

                            if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])) {
                                $profile_src = $path . $_SESSION['profile_pic'];
                            } else {
                                $profile_src = $default_pic; 
                            }
                            ?>
                            
                            <img src="<?php echo $profile_src; ?>" alt="Profile" 
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>
                    </div>

                    <div id="myDropdown" class="profile-dropdown-menu">
                        <a href="<?php echo $path; ?>user/profile.php">My Profile</a>
                        <hr style="margin:0; border:0; border-top:1px solid #eee;">
                        <a href="<?php echo $path; ?>includes/logout.php" class="logout-link">Logout</a>
                    </div>

                </div>

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
                <a href="<?php echo $path; ?>user/user_dashboard.php" class="<?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            <?php else: ?>
                <a href="<?php echo $path; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
            <?php endif; ?>
            
            <a href="<?php echo $path; ?>user/quests.php" class="<?php echo ($current_page == 'quests.php') ? 'active' : ''; ?>">Quests</a>
            <a href="<?php echo $path; ?>user/leaderboard.php" class="<?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">Leaderboard</a>
            <a href="<?php echo $path; ?>user/shop.php" class="<?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">Reward Shop</a>
            
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

        function toggleProfileDropdown(event) {
            event.stopPropagation(); 
            document.getElementById("myDropdown").classList.toggle("show-dropdown");
        }

        window.onclick = function(event) {
            if (!event.target.closest('.user-profile-container')) {
                var dropdowns = document.getElementsByClassName("profile-dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show-dropdown')) {
                        openDropdown.classList.remove('show-dropdown');
                    }
                }
            }
        }
    </script>
</body>
</html>