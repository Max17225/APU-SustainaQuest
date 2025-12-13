<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustainaQuest - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="main-header">
        <div class="logo-container">
            <img src="assets/image/SustainaQuest Logo.png" alt="SustainaQuest Logo" class="logo-img" style="width: 100x; height: 100px; object-fit: contain;"> 
            <a href="index.php" style="text-decoration:none; color:white;">
                <span class="site-title">SustainaQuest</span>
            </a>
        </div>

        <nav class="main-nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="login.php" class="nav-link">Quests</a>
            <a href="login.php" class="nav-link">Leaderboard</a>
            <a href="login.php" class="nav-link">Reward Shop</a>
        </nav>

        <div class="auth-action">
            <a href="login.php" class="btn-login">Login/Register</a>
        </div>
    </header>