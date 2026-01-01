<?php
// ==========================
//       Session Check
// ==========================
session_start();
require_once __DIR__ . '/includes/general_function.php';

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: ' . resolve_location('admin'));
            exit;

        case 'moderator':
            header('Location: ' . resolve_location('mod_dashboard.php'));
            exit;

        default:
            header('Location: ' . resolve_location('user_dashboard.php'));
            exit;
    }
?>

<?php include 'includes/header.php'; ?>

<section class="hero-section">
    <div class="hero-overlay">
        <div class="hero-content">
            <h1>Make a Difference. Get Rewarded</h1>
            <p>Join APU Green Points & Rewards Challenge System Today!</p>
            <a href="auth_page/register.php" class="btn-register-hero">Register Now!</a>
        </div>
    </div>
</section>

<div class="content-wrapper">

    <div class="about-card">
        <h2>About SustainaQuest 
            <img src="assets/image/leaf.png" alt="Leaf" style="width: 24px; height: 24px; display: inline-block; vertical-align: middle; margin-left: 5px;">
        </h2>
        <p>The APU Green Point Challenge is a university-wide program designed to engage students and staff in sustainability. Complete eco-friendly quests, earn Green Points, climb the leaderboard, and redeem exciting rewards-all while making a positive impact on our planet.</p>
    </div>

    <div class="how-it-works-container">
        <h2>How it Works</h2>
        
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon-slot">
                    <img src="assets/image/tick.png" alt="Quest" style="width: 40px; height: 40px;">
                </div>
                <div class="step-text">
                    <h3>Complete Quests</h3>
                    <p>Take part in eco-friendly challenges</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-icon-slot">
                     <img src="assets/image/camera.png" alt="Upload" style="width: 45px; height: 35px;">
                </div>
                <div class="step-text">
                    <h3>Submit Evidence</h3>
                    <p>Upload Photos/Videos to prove your participation</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-icon-slot">
                     <img src="assets/image/trophy.png" alt="Points" style="width: 40px; height: 40px;">
                </div>
                <div class="step-text">
                    <h3>Earn Green Points</h3>
                    <p>Climb the Leaderboard & earn badges</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-icon-slot">
                     <img src="assets/image/present.png" alt="Gift" style="width: 40px; height: 40px;">
                </div>
                <div class="step-text">
                    <h3>Redeem Rewards</h3>
                    <p>Exchange Green Points for exciting rewards.</p>
                </div>
            </div>

        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
