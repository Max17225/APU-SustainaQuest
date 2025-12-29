<?php
// 1. SESSION & AUTH
session_start();
require_once '../includes/session_check.php';

require_login(); 

$user_id = $_SESSION['user_id'];

// 2. INCLUDES & SETTINGS
$path = "../";
$page_css = "profile.css"; 

require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once 'user_functions.php';

// ================= FORM PROCESSING =================
$msg = "";
$msg_type = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // A. UPDATE EMAIL
    if (isset($_POST['update_email'])) {
        $new_email = filter_var(trim($_POST['new_email']), FILTER_SANITIZE_EMAIL);
        
        if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE userId = ?");
            $stmt->bind_param("si", $new_email, $user_id);
            
            if ($stmt->execute()) {
                $msg = "Email updated successfully.";
                $msg_type = "success";
            } else {
                $msg = "Database error. Please try again.";
                $msg_type = "error";
            }
        } else {
            $msg = "Invalid email format.";
            $msg_type = "error";
        }
    }

    // B. UPDATE PASSWORD
    if (isset($_POST['update_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (!empty($new_pass) && $new_pass === $confirm_pass) {
            
            // 1. Fetch current password hash from DB
            $stmt = $conn->prepare("SELECT passwordHash FROM users WHERE userId = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $db_hash = $row['passwordHash'];

                // 2. Verify Current Password
                if (password_verify($current_pass, $db_hash)) {
                    
                    // 3. Update to New Password
                    $new_hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                    
                    $update_stmt = $conn->prepare("UPDATE users SET passwordHash = ? WHERE userId = ?");
                    $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $msg = "Password changed successfully.";
                        $msg_type = "success";
                    } else {
                        $msg = "Database error. Please try again.";
                        $msg_type = "error";
                    }

                } else {
                    $msg = "Incorrect current password.";
                    $msg_type = "error";
                }
            } else {
                $msg = "User not found.";
                $msg_type = "error";
            }
        } else {
            $msg = "New passwords do not match or are empty.";
            $msg_type = "error";
        }
    }
}

// ================= FETCH USER DATA =================
$stmt = $conn->prepare("SELECT userName, email FROM users WHERE userId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Avatar Logic (Default)
$avatar_url = "../assets/image/profile_picture.png";
?>

<div class="profile-main-container">
    
    <div class="profile-card">

        <?php if ($msg): ?>
            <div class="alert-box alert-<?= $msg_type ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <img src="<?= $avatar_url ?>" alt="Profile" class="profile-avatar">
            
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['userName']) ?></h2>
            </div>
        </div>

        <hr class="divider">

        <h3 class="section-title">Account Details</h3>

        <div class="input-group">
            <div class="input-display">
                <span style="color:#888; margin-right:5px; font-size: 0.9em;">Email:</span> 
                <?= htmlspecialchars($user['email']) ?>
            </div>
            <button class="btn-change" onclick="open_modal('email')">Change</button>
        </div>

        <div class="input-group">
            <div class="input-display">
                <span style="color:#888; margin-right:5px; font-size: 0.9em;">Password:</span> 
                ••••••••••••
            </div>
            <button class="btn-change" onclick="open_modal('password')">Change</button>
        </div>

    </div>
</div>

<div id="modal-email" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Email Address</h3>
            <span class="close-modal" onclick="close_modal('email')">&times;</span>
        </div>
        
        <form method="POST" action="">
            <label style="font-weight:600; color:#555;">New Email Address</label>
            <input type="email" name="new_email" required 
                   class="input-display" 
                   style="width: 100%; box-sizing: border-box; margin-top: 8px; margin-bottom: 20px;"
                   placeholder="Enter new email">
            
            <div class="modal-btn-group">
                <button type="button" class="btn-change" onclick="close_modal('email')">Cancel</button>
                <button type="submit" name="update_email" class="btn-primary">Update Email</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-password" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Password</h3>
            <span class="close-modal" onclick="close_modal('password')">&times;</span>
        </div>
        
        <form method="POST" action="">
            
            <label style="font-weight:600; color:#555;">Current Password</label>
            <input type="password" name="current_password" required 
                   class="input-display" 
                   style="width: 100%; box-sizing: border-box; margin-top: 8px; margin-bottom: 15px;"
                   placeholder="Enter your current password">

            <label style="font-weight:600; color:#555;">New Password</label>
            <input type="password" name="new_password" required 
                   class="input-display" 
                   style="width: 100%; box-sizing: border-box; margin-top: 8px; margin-bottom: 15px;"
                   placeholder="Minimum 8 characters">
            
            <label style="font-weight:600; color:#555;">Confirm New Password</label>
            <input type="password" name="confirm_password" required 
                   class="input-display" 
                   style="width: 100%; box-sizing: border-box; margin-top: 8px; margin-bottom: 20px;"
                   placeholder="Re-enter new password">
            
            <div class="modal-btn-group">
                <button type="button" class="btn-change" onclick="close_modal('password')">Cancel</button>
                <button type="submit" name="update_password" class="btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function open_modal(type) {
        document.getElementById('modal-' + type).style.display = 'flex';
    }

    function close_modal(type) {
        document.getElementById('modal-' + type).style.display = 'none';
    }

    window.onclick = function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.style.display = 'none';
        }
    }
</script>

<?php include '../includes/footer.php'; ?>