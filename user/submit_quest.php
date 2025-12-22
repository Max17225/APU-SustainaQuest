<?php
// ============================================================
// QUEST SUBMISSION HANDLER (SERVER-SIDE)
// ============================================================
// This file handles:
// - User authentication check
// - Receiving quest submission data
// - Processing evidence image & optional video uploads
// - Determining AI-based approval or pending status
// - Saving submission records into the database
// - Awarding EXP and Green Points for instant approvals
// ============================================================

session_start();
require_once '../includes/db_connect.php';

// ============================================================
// 1. AUTHENTICATION CHECK
// ------------------------------------------------------------
// Ensure the user is logged in before allowing quest submission
// ============================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth_page/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ============================================================
// 2. FORM SUBMISSION VALIDATION
// ------------------------------------------------------------
// Ensure the request is POST and an evidence image is provided
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["evidence_file"])) {
    
    // Retrieve submitted quest ID safely
    $quest_id = intval($_POST['quest_id']);
    
    // --------------------------------------------------------
    // AI VERIFICATION FLAG
    // --------------------------------------------------------
    // Value is sent from JavaScript:
    // 1 = AI verified successfully
    // 0 = Not verified / skipped (e.g. Weekly quests)
    // --------------------------------------------------------
    $is_ai_verified = isset($_POST['ai_verified']) ? intval($_POST['ai_verified']) : 0;

    // ========================================================
    // 3. FETCH QUEST REWARD DATA
    // --------------------------------------------------------
    // Needed to award points and EXP upon approval
    // ========================================================
    $stmt = $conn->prepare("SELECT type, pointReward, expReward FROM quests WHERE questId = ?");
    $stmt->bind_param("i", $quest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quest_data = $result->fetch_assoc();

    $points = $quest_data['pointReward'];
    $exp = $quest_data['expReward'];

    // ========================================================
    // 4. FILE UPLOAD DIRECTORY SETUP
    // --------------------------------------------------------
    // Evidence files are stored in a dedicated folder
    // ========================================================
    $target_dir = "../assets/evidence/";
    if (!file_exists($target_dir)) { 
        mkdir($target_dir, 0777, true); 
    }

    // ========================================================
    // A. IMAGE UPLOAD HANDLING (REQUIRED)
    // ========================================================
    $img_db_path = ""; // Stores relative path for database
    $evidence_file = $_FILES["evidence_file"];
    
    if (!empty($evidence_file['name'])) {

        // Extract file extension
        $img_ext = strtolower(pathinfo($evidence_file["name"], PATHINFO_EXTENSION));

        // Generate unique filename to prevent overwrite
        $new_img_name = time() . "_img_" . basename($evidence_file["name"]);
        $target_img_path = $target_dir . $new_img_name;
        
        // Move uploaded image to target directory
        if (move_uploaded_file($evidence_file["tmp_name"], $target_img_path)) {
            $img_db_path = "assets/evidence/" . $new_img_name;
        } else {
            die("Error uploading image file.");
        }
    }

    // ========================================================
    // B. VIDEO UPLOAD HANDLING (OPTIONAL - WEEKLY QUESTS)
    // ========================================================
    $vid_db_path = null; // Default value when no video is uploaded
    
    if (isset($_FILES["video_file"]) && !empty($_FILES["video_file"]["name"])) {

        $video_file = $_FILES["video_file"];
        $vid_ext = strtolower(pathinfo($video_file["name"], PATHINFO_EXTENSION));
        
        // Allowed video formats for safety
        $allowed_videos = ['mp4', 'webm', 'mov', 'ogg'];
        
        if (in_array($vid_ext, $allowed_videos)) {

            // Generate unique filename
            $new_vid_name = time() . "_vid_" . basename($video_file["name"]);
            $target_vid_path = $target_dir . $new_vid_name;
            
            // Move uploaded video file
            if (move_uploaded_file($video_file["tmp_name"], $target_vid_path)) {
                $vid_db_path = "assets/evidence/" . $new_vid_name;
            }
        }
    }

    // ========================================================
    // 5. DETERMINE SUBMISSION STATUS
    // --------------------------------------------------------
    // - AI verified submissions are auto-approved
    // - Others remain pending for manual review
    // ========================================================
    $status = ($is_ai_verified === 1) ? 'Approved' : 'Pending';
    $verified_by_ai = ($is_ai_verified === 1) ? 1 : 0;

    // ========================================================
    // 6. SAVE SUBMISSION TO DATABASE
    // --------------------------------------------------------
    // Includes image, optional video, AI flag, and status
    // ========================================================
    $sql = "INSERT INTO questsubmissions 
            (questId, submittedByUserId, evidencePictureURL, evidenceVideoURL, approveStatus, verifiedByAi, submitDate) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    // Parameter binding:
    // i = integer, s = string
    // questId, userId, imagePath, videoPath, status, aiFlag
    $stmt->bind_param("iisssi", $quest_id, $user_id, $img_db_path, $vid_db_path, $status, $verified_by_ai);
    
    if ($stmt->execute()) {
        
        // ====================================================
        // 7. AWARD POINTS & EXP (AUTO-APPROVED ONLY)
        // ====================================================
        if ($status === 'Approved') {
            
            // A. Award Green Points (Simple Addition)
            $upd = $conn->prepare("UPDATE users SET greenPoints = greenPoints + ? WHERE userId = ?");
            $upd->bind_param("ii", $points, $user_id);
            $upd->execute();

            // B. Award XP & Handle Level Up (Using user_functions.php)
            // --------------------------------------------------------
            // This replaces the simple UPDATE query for levels.
            // It handles the exponential curve and level-up overflow.
            // --------------------------------------------------------
            
            // Check location of user_functions.php
            if (file_exists('../includes/user_functions.php')) {
                require_once '../includes/user_functions.php';
            } else {
                // Fallback if file is in the same directory
                require_once 'user_functions.php';
            }

            // Call the function to add XP and check for level up
            // Note: This function ALSO checks for badges internally if a level up occurs!
            add_xp_and_level_up($conn, $user_id, $exp);
        }

        // Redirect back to quests page with success indicator
        header("Location: quests.php?success=1");
        exit();
    } else {
        echo "Database Error: " . $stmt->error;
    }
}
?>