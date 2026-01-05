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
require_once '../includes/session_check.php';
require_once '../includes/db_connect.php';

// 1. AUTHENTICATION CHECK
// ------------------------------------------------------------
require_login(); 
$user_id = $_SESSION['user_id'];

// 2. FORM SUBMISSION VALIDATION
// ------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve submitted quest ID safely
    $quest_id = isset($_POST['quest_id']) ? intval($_POST['quest_id']) : 0;
    
    // AI Verification Flag (1 = Verified, 0 = Not/Skipped)
    $is_ai_verified = isset($_POST['ai_verified']) ? intval($_POST['ai_verified']) : 0;

    // ========================================================
    // 3. FETCH QUEST REWARD DATA
    // ========================================================
    $stmt = $conn->prepare("SELECT type, pointReward, expReward FROM quests WHERE questId = ?");
    $stmt->bind_param("i", $quest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quest_data = $result->fetch_assoc();
    $stmt->close();

    if (!$quest_data) {
        header("Location: quests.php?error=QuestNotFound");
        exit();
    }

    $points = $quest_data['pointReward'];
    $exp = $quest_data['expReward'];

    // ========================================================
    // 4. FILE UPLOAD DIRECTORY SETUP
    // ========================================================
    $target_dir = "../assets/uploads/";
    if (!file_exists($target_dir)) { 
        mkdir($target_dir, 0777, true); 
    }

    // ========================================================
    // A. IMAGE UPLOAD HANDLING (REQUIRED)
    // ========================================================
    // Constraint: You cannot submit if picture is not submitted.
    if (!isset($_FILES["evidence_file"]) || $_FILES["evidence_file"]["error"] != UPLOAD_ERR_OK) {
        // Redirect with error if image is missing or failed
        header("Location: quests.php?error=ImageRequired");
        exit();
    }

    $img_db_path = ""; 
    $evidence_file = $_FILES["evidence_file"];
    $img_ext = strtolower(pathinfo($evidence_file["name"], PATHINFO_EXTENSION));
    $new_img_name = time() . "_img_" . uniqid() . "." . $img_ext; // Added uniqid for safety
    $target_img_path = $target_dir . $new_img_name;
    
    if (move_uploaded_file($evidence_file["tmp_name"], $target_img_path)) {
        $img_db_path = "assets/uploads/" . $new_img_name;
    } else {
        header("Location: quests.php?error=ImageUploadFailed");
        exit();
    }

    // ========================================================
    // B. VIDEO UPLOAD HANDLING (OPTIONAL)
    // ========================================================
    $vid_db_path = null; 
    
    if (isset($_FILES["video_file"]) && !empty($_FILES["video_file"]["name"])) {
        $video_file = $_FILES["video_file"];
        
        // Check for upload errors (e.g., file too large)
        if ($video_file["error"] === UPLOAD_ERR_OK) {
            $vid_ext = strtolower(pathinfo($video_file["name"], PATHINFO_EXTENSION));
            $allowed_videos = ['mp4', 'webm', 'mov', 'ogg'];
            
            if (in_array($vid_ext, $allowed_videos)) {
                $new_vid_name = time() . "_vid_" . uniqid() . "." . $vid_ext;
                $target_vid_path = $target_dir . $new_vid_name;
                
                if (move_uploaded_file($video_file["tmp_name"], $target_vid_path)) {
                    $vid_db_path = "assets/uploads/" . $new_vid_name;
                }
            }
        } elseif ($video_file["error"] === UPLOAD_ERR_INI_SIZE || $video_file["error"] === UPLOAD_ERR_FORM_SIZE) {
        }
    }

    // ========================================================
    // 5. DETERMINE SUBMISSION STATUS
    // ========================================================
    $status = ($is_ai_verified === 1) ? 'Completed' : 'Pending';
    $verified_by_ai = ($is_ai_verified === 1) ? 1 : 0;

    // If Approved, set verifyDate to NOW(). If Pending, keep it NULL.
    // We handle this via the SQL query logic or variable.
    $verify_date_val = ($status === 'Completed') ? date('Y-m-d H:i:s') : null;

    // ========================================================
    // 6. SAVE SUBMISSION TO DATABASE
    // ========================================================
    $sql = "INSERT INTO questsubmissions 
            (questId, submittedByUserId, evidencePictureURL, evidenceVideoURL, approveStatus, verifiedByAi, submitDate, verifyDate) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: i=int, i=int, s=string, s=string, s=string, i=int, s=string (verifyDate)
    $stmt->bind_param("iisssis", $quest_id, $user_id, $img_db_path, $vid_db_path, $status, $verified_by_ai, $verify_date_val);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // ====================================================
        // 7. AWARD POINTS & EXP (AUTO-APPROVED ONLY)
        // ====================================================
        if ($status === 'Completed') {
            
            // A. Award Green Points
            $upd = $conn->prepare("UPDATE users SET greenPoints = greenPoints + ? WHERE userId = ?");
            $upd->bind_param("ii", $points, $user_id);
            $upd->execute();
            $upd->close();

            // B. Award XP & Handle Level Up
            if (file_exists('../includes/user_functions.php')) {
                require_once '../includes/user_functions.php';
            } else {
                require_once 'user_functions.php';
            }
            
            add_xp_and_level_up($conn, $user_id, $exp);
        }

        header("Location: quests.php?success=1");
        exit();

    } else {
        // Database error
        header("Location: quests.php?error=DatabaseError");
        exit();
    }
} else {
    // If accessed directly without POST
    header("Location: quests.php");
    exit();
}
?>