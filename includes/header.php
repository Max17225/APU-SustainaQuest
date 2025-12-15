<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($path)) { $path = ""; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustainaQuest - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
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
            <a href="<?php echo $path; ?>index.php" class="nav-link">Home</a>
            <a href="<?php echo $path; ?>user/quests.php" class="nav-link">Quests</a>
            <a href="<?php echo $path; ?>leaderboard.php" class="nav-link">Leaderboard</a>
            <a href="<?php echo $path; ?>shop.php" class="nav-link">Reward Shop</a>
        </nav>

        <div class="auth-action">
            <a href="<?php echo $path; ?>login.php" class="btn-login">Login/Register</a>
        </div>
    </header>

    <div id="mobile-sidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="toggle_sidebar()">&times;</a>
        
        <div class="sidebar-logo-container">
            <img src="<?php echo $path; ?>assets/image/SustainaQuest Logo.png" alt="Sidebar Logo">
        </div>

        <div class="sidebar-content">
            <a href="<?php echo $path; ?>index.php">Home</a>
            <a href="<?php echo $path; ?>user/quests.php">Quests</a>
            <a href="<?php echo $path; ?>leaderboard.php">Leaderboard</a>
            <a href="<?php echo $path; ?>shop.php">Reward Shop</a>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 5px 20px;">
            
            <a href="<?php echo $path; ?>login.php" class="sidebar-btn-login">Login / Register</a>
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