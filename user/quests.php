<?php 
// 1. SESSION HANDLING
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. INCLUDES & SETTINGS
$path = "../";
$page_css = "quests.css";

include '../includes/db_connect.php';
include '../includes/header.php';
include 'user_functions.php'; 

// ================= FETCH DATA (USING FUNCTIONS FROM user_functions.php) =================

// 1. Get Available Quests (Daily & Weekly)
$quests_data = get_available_quests($conn);
$daily_quests = $quests_data['daily'];
$weekly_quests = $quests_data['weekly'];

// 2. Initialize User Data
$pending_quests = [];
$completed_quests = [];
$rejected_quests = [];
$locked_quest_ids = [];

$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    $uid = $_SESSION['user_id'];

    // 3. Get Locked Quest IDs (Logic for 24hr cooldown / Weekly check)
    $locked_quest_ids = get_locked_quest_ids($conn, $uid);

    // 4. Get Quest History (Pending, Completed, Rejected)
    $history_data = get_user_quest_history($conn, $uid);
    $pending_quests = $history_data['pending'];
    $completed_quests = $history_data['completed'];
    $rejected_quests = $history_data['rejected'];
}
?>

<div class="quest-main-container">

    <div class="main-toggle-container">
        <button class="main-toggle-btn active" id="btn-available" onclick="switch_main_view('available')">Available Quests</button>
        <button class="main-toggle-btn" id="btn-history" onclick="switch_main_view('history')">Quests History</button>
    </div>

    <div id="view-available">
        <div class="sub-tabs-container">
            <span class="sub-tab-link active" onclick="switch_sub_view('daily', this)">Daily</span>
            <span class="sub-tab-link" onclick="switch_sub_view('weekly', this)">Weekly</span>
        </div>

        <div id="grid-daily" class="quests-grid">
            <?php foreach ($daily_quests as $q): 
                // Check if this quest is locked
                $is_locked = in_array((string)$q['questId'], $locked_quest_ids);
            ?>
                <div class="quest-card">

                    <div class="card-content-top">

                        <div class="card-img-box">
                            <img src="<?php echo $path . ($q['questIconURL'] ?? 'assets/image/leaf.png'); ?>">
                        </div>

                         <h3><?php echo $q['title']; ?></h3>

                         <div class="quest-stats">
                            EXP: <?php echo $q['expReward']; ?><br>
                            Green Points: <?php echo $q['pointReward']; ?>
                        </div>
                    </div>

                    <?php if ($is_locked): ?>
                        <button class="btn-primary" disabled style="background-color: #ccc; cursor: not-allowed; opacity: 0.7;">
                            Completed
                        </button>
                    <?php else: ?>
                        <button class="btn-primary" 
                            onclick="open_modal(this, 'available')"
                            data-type="Daily" 
                            data-id="<?php echo $q['questId']; ?>"
                            data-title="<?php echo htmlspecialchars($q['title']); ?>"
                            data-desc="<?php echo htmlspecialchars($q['description']); ?>"
                            data-exp="<?php echo $q['expReward']; ?>"
                            data-points="<?php echo $q['pointReward']; ?>">
                            View Details
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
             <?php if(empty($daily_quests)) echo "<p>No quests available.</p>"; ?>
        </div>

        <div id="grid-weekly" class="quests-grid" style="display:none;">
            <?php foreach ($weekly_quests as $q): 
                // Check if this quest is locked
                $is_locked = in_array((string)$q['questId'], $locked_quest_ids);
            ?>
                <div class="quest-card">
                    <div class="card-content-top">
                        <div class="card-img-box">
                            <img src="<?php echo $path . ($q['questIconURL'] ?? 'assets/image/leaf.png'); ?>">
                        </div>
                        <h3><?php echo $q['title']; ?></h3>
                        <div class="quest-stats">
                            EXP: <?php echo $q['expReward']; ?><br>
                            Green Points: <?php echo $q['pointReward']; ?>
                        </div>
                    </div>

                    <?php if ($is_locked): ?>
                        <button class="btn-primary" disabled style="background-color: #ccc; cursor: not-allowed; opacity: 0.7;">
                            Completed / Pending
                        </button>
                    <?php else: ?>
                        <button class="btn-primary" 
                            onclick="open_modal(this, 'available')"
                            data-type="Weekly" 
                            data-id="<?php echo $q['questId']; ?>"
                            data-title="<?php echo htmlspecialchars($q['title']); ?>"
                            data-desc="<?php echo htmlspecialchars($q['description']); ?>"
                            data-exp="<?php echo $q['expReward']; ?>"
                            data-points="<?php echo $q['pointReward']; ?>">
                            View Details
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        
        <?php if(empty($weekly_quests)) echo "<p>No quests available.</p>"; ?>
        </div>
    </div>

    <div id="view-history" style="display:none;">

        <?php if ($is_logged_in): ?>

             <div class="sub-tabs-container">
                <span class="sub-tab-link active" onclick="switch_history_sub('completed', this)">Completed</span>
                <span class="sub-tab-link" onclick="switch_history_sub('rejected', this)">Rejected</span>
                <span class="sub-tab-link" onclick="switch_history_sub('pending', this)">Pending</span>
            </div>

            <?php 
            function render_history_grid($id, $quests, $status_class, $path) {
                echo "<div id='$id' class='quests-grid' " . ($id != 'hist-completed' ? "style='display:none;'" : "") . ">";
                foreach ($quests as $q) {

                    // Prepare data for display
                    $title = htmlspecialchars($q['title']);
                    $desc = htmlspecialchars($q['description']);
                    $sub_id = $q['submissionId'];
                    $sub_date = date('d M Y', strtotime($q['submitDate']));
                    $status = $q['approveStatus'];
                    $evid_url = $path . ($q['evidencePictureURL'] ?? '');
                    
                    $ver_date = $q['verifyDate'] ? date('d M Y', strtotime($q['verifyDate'])) : '';
                    
                    // GET VIDEO URL
                    $video_url = !empty($q['evidenceVideoURL']) ? $path . $q['evidenceVideoURL'] : '';

                    if (isset($q['verifiedByAi']) && $q['verifiedByAi'] == 1) {
                        // 1. AI Verified
                        $ver_by = "🤖 AI System";
                        if(empty($q['verifyDate'])) {
                            $ver_date = $sub_date; 
                        }
                    } 
                    elseif (!empty($q['verifiedByModeratorId'])) {
                        // 2. Moderator Verified (Use Name if found, otherwise ID)
                        $modName = !empty($q['modName']) ? $q['modName'] : ("#" . $q['verifiedByModeratorId']);
                        $ver_by = "Moderator: " . htmlspecialchars($modName);
                    } 
                    elseif (!empty($q['verifiedByAdminId'])) {
                        // 3. Admin Verified
                        $ver_by = "Admin";
                    } 
                    else {
                        // 4. Pending
                        $ver_by = "Pending";
                    }

                    $reason = htmlspecialchars($q['declinedReason'] ?? '');

                    // Prints all the data from previous step into:
                    echo "
                    <div class='quest-card'>
                        <div class='card-content-top'>
                            <span class='status-badge $status_class'>" . $status . "</span>
                            <span class='quest-type-label'>" . $q['type'] . "</span>
                            <div class='card-img-box'>
                                <img src='" . $path . ($q['questIconURL'] ?? 'assets/image/leaf.png') . "'>
                            </div>
                            <h3>" . $title . "</h3>
                            <div class='quest-stats'>Submitted: " . $sub_date . "</div>
                        </div>
                        
                        <button class='btn-primary' 
                            onclick='open_modal(this, \"history\")'
                            data-title='$title'
                            data-desc='$desc'
                            data-exp='{$q['expReward']}'
                            data-points='{$q['pointReward']}'
                            data-sub-id='$sub_id' 
                            data-sub-date='$sub_date'
                            data-status='$status'
                            data-evidence='$evid_url'
                            data-video='$video_url' 
                            data-ver-date='$ver_date'
                            data-ver-by='$ver_by'
                            data-reason='$reason'>
                            View Details
                        </button>
                    </div>";
                }
                if(empty($quests)) echo "<p>No items available.</p>";
                echo "</div>";
            }
            ?>

            <?php render_history_grid('hist-completed', $completed_quests, 'status-completed', $path); ?>
            <?php render_history_grid('hist-rejected', $rejected_quests, 'status-rejected', $path); ?>
            <?php render_history_grid('hist-pending', $pending_quests, 'status-pending', $path); ?>

        <?php else: ?>

            <div style="padding: 60px 20px; text-align: center; color: #555; background: white; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
                <img src="../assets/image/trophy.png" style="width: 60px; margin-bottom: 20px; opacity: 0.6;">
                <h2 style="margin-bottom: 10px; color: #264633;">Track Your Progress</h2>
                <p style="font-size: 1.1rem;">Please log in to view your quest history, track submissions, and see your rewards.</p>
                <br>
                <a href="../auth_page/login.php" class="btn-primary" style="padding: 12px 30px; display: inline-block; width: auto; text-decoration: none;">Login / Register</a>
            </div>

        <?php endif; ?>
    </div>
</div> 

<div id="quest-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Quest Details</h3>
            <span class="close-modal" onclick="close_modal()">&times;</span>
        </div>

        <h2 id="m-title">Title</h2>

        <div class="quest-stats" style="text-align:left; background:none; padding:0; margin-bottom:10px;">
            <p><strong>Rewards:</strong> <span id="m-exp"></span> EXP, <span id="m-points"></span> Green Points</p>
        </div>

        <div id="modal-section-available" style="display:none;">
            <h4>Instructions:</h4>
            <p id="m-desc" style="color:#666; margin-bottom:15px; white-space: pre-line;"></p>
            <hr>
            
            <?php if ($is_logged_in): ?>
                <form id="quest-form" action="submit_quest.php" method="POST" enctype="multipart/form-data" style="margin-top:15px;">
                    <input type="hidden" name="quest_id" id="m-quest-id">
                    
                    <label>Upload Evidence Image:</label><br>
                    <input type="file" id="evidence-input" name="evidence_file" accept="image/*" required style="margin-top:10px;">
                    
                    <div id="extra-video-container" style="display:none; margin-top:15px; border-top:1px dashed #ccc; padding-top:10px;">
                        <label style="color:#d35400;">(Weekly Only) Upload Video Evidence:</label><br>
                        <span style="font-size:0.8rem; color:#666;">Optional: Show us a video of your activity (Maximum File Size 2MB).</span><br>
                        <input type="file" id="video-input" name="video_file" accept="video/mp4,video/webm">
                    </div>

                    <br><br>
                    <button type="submit" class="btn-primary">Submit Quest</button>
                </form>
            <?php else: ?>
                <div style="margin-top: 20px; text-align: center;">
                    <p style="margin-bottom: 10px; color: #666;">Want to earn points?</p>
                    <a href="../auth_page/login.php" class="btn-primary" style="text-decoration:none; display:block; padding:12px 0;">Login to Participate</a>
                </div>
            <?php endif; ?>
        </div>

        <div id="modal-section-history" style="display:none;">
            <hr>
            <div class="detail-row"><span class="detail-label">Submission ID:</span> <span id="h-sub-id"></span></div>
            <div class="detail-row"><span class="detail-label">Status:</span> <span id="h-status"></span></div>
            <div class="detail-row"><span class="detail-label">Submitted On:</span> <span id="h-sub-date"></span></div>
            
            <div id="h-verification-box" style="display:none;">
                <div class="detail-row"><span class="detail-label">Verified Date:</span> <span id="h-ver-date"></span></div>
                <div class="detail-row"><span class="detail-label">Verified By:</span> <span><span id="h-ver-by"></span></span></div>
            </div>

            <div id="h-reason-box" style="display:none; background:#ffebee; padding:10px; border-radius:4px; margin:10px 0;">
                <span class="detail-label" style="color:#c62828;">Declined Reason:</span> 
                <span id="h-reason" style="color:#c62828;"></span>
            </div>

            <div style="margin-top:15px;">
                <strong>Instructions:</strong>
                <p id="h-desc-text" style="color:#666; font-size:0.9rem; margin-top:5px; white-space: pre-line;"></p>
            </div>

            <div style="margin-top: 15px;">
                <strong>Submitted Evidence:</strong><br>

                <!-- IMAGE PREVIEW -->
                <img id="h-evidence-img"
                    style="display:none; max-width:100%; border-radius:8px; margin-top:10px;">

                <!-- VIDEO PREVIEW -->
                <video id="h-evidence-video"
                    controls
                    style="display:none; max-width:100%; border-radius:8px; margin-top:10px;">
                </video>
            </div>

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet"></script>

<script>
    // ==========================================
    // 1. VIEW SWITCHERS
    // ==========================================
    function switch_main_view(view) {
        document.getElementById('btn-available').classList.remove('active');
        document.getElementById('btn-history').classList.remove('active');
        document.getElementById('btn-' + view).classList.add('active');
        document.getElementById('view-available').style.display = (view === 'available') ? 'block' : 'none';
        document.getElementById('view-history').style.display = (view === 'history') ? 'block' : 'none';
    }

    function switch_sub_view(type, btn) {
        document.querySelectorAll('#view-available .sub-tab-link').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('grid-daily').style.display = 'none';
        document.getElementById('grid-weekly').style.display = 'none';
        document.getElementById('grid-' + type).style.display = 'grid';
    }
    
    function switch_history_sub(type, btn) {
        document.querySelectorAll('#view-history .sub-tab-link').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('hist-completed').style.display = 'none';
        document.getElementById('hist-rejected').style.display = 'none';
        document.getElementById('hist-pending').style.display = 'none';
        document.getElementById('hist-' + type).style.display = 'grid';
    }

    // ==========================================
    // 2. MODAL LOGIC
    // ==========================================
    let currentQuestType = ""; 

    function open_modal(btn, type) {
        var title = btn.getAttribute('data-title');
        var desc = btn.getAttribute('data-desc');
        var exp = btn.getAttribute('data-exp');
        var points = btn.getAttribute('data-points');
        var id = btn.getAttribute('data-id');

        currentQuestType = btn.getAttribute('data-type'); 

        document.getElementById('m-title').innerText = title;
        document.getElementById('m-exp').innerText = exp;
        document.getElementById('m-points').innerText = points;
        
        if (type === 'available') {
            document.getElementById('modal-section-available').style.display = 'block';
            document.getElementById('modal-section-history').style.display = 'none';
            document.getElementById('m-desc').innerText = desc;
            if(document.getElementById('m-quest-id')) document.getElementById('m-quest-id').value = id;

            // Check if the button exists before trying to change it
            // === SHOW/HIDE VIDEO SUBMISSION ===
            const videoContainer = document.getElementById('extra-video-container');
            const submitBtn = document.querySelector('#quest-form button');
            
            // SAFETY CHECK: Only run this if the container exists (Logged In Users)
            if (videoContainer) {
                const videoInput = document.getElementById('video-input');

                if (currentQuestType === 'Weekly') {
                    // WEEKLY: Show Video Input
                    videoContainer.style.display = 'block';
                    if(submitBtn) submitBtn.innerText = "Submit for Review";
                } else {
                    // DAILY: Hide Video Input
                    videoContainer.style.display = 'none';
                    // Clear the value safely
                    if (videoInput) videoInput.value = ""; 
                    if(submitBtn) submitBtn.innerText = "Scan & Submit";
                }
            }
        } else {
            // HISTORY LOGIC
            document.getElementById('modal-section-available').style.display = 'none';
            document.getElementById('modal-section-history').style.display = 'block';

            document.getElementById('h-sub-id').innerText = btn.getAttribute('data-sub-id');
            document.getElementById('h-status').innerText = btn.getAttribute('data-status');
            document.getElementById('h-sub-date').innerText = btn.getAttribute('data-sub-date');
            document.getElementById('h-desc-text').innerText = desc;

            // =======================================
            // EVIDENCE PREVIEW (IMAGE / VIDEO)
            // =======================================
            const img = document.getElementById('h-evidence-img');
            const video = document.getElementById('h-evidence-video');

            // Reset previous preview
            img.style.display = 'none';
            video.style.display = 'none';
            img.src = '';
            video.src = '';

            // Get evidence URLs
            const imageUrl = btn.getAttribute('data-evidence');
            const videoUrl = btn.getAttribute('data-video');

            // Show image if exists
            if (imageUrl && imageUrl !== '') {
                img.src = imageUrl;
                img.style.display = 'block';
            }

            // Show video if exists (weekly quests)
            if (videoUrl && videoUrl !== '') {
                video.src = videoUrl;
                video.style.display = 'block';
            }

            var verDate = btn.getAttribute('data-ver-date');
            if (verDate && verDate !== '') {
                document.getElementById('h-verification-box').style.display = 'block';
                document.getElementById('h-ver-date').innerText = verDate;
                document.getElementById('h-ver-by').innerText = btn.getAttribute('data-ver-by');
            } else {
                document.getElementById('h-verification-box').style.display = 'none';
            }

            var reason = btn.getAttribute('data-reason');
            if (reason) {
                document.getElementById('h-reason-box').style.display = 'block';
                document.getElementById('h-reason').innerText = reason;
            } else {
                document.getElementById('h-reason-box').style.display = 'none';
            }
        }
        document.getElementById('quest-modal').style.display = 'flex';
    }

    function close_modal() {
        document.getElementById('quest-modal').style.display = 'none';
    }

    // ==========================================
    // 3. AI LOGIC
    // ==========================================

    let ai_model = null;

    // Load AI Model Function
    (async function load_model() {
        console.log("Loading AI...");
        // Store in ai_model so it can be reused, "await" is used to let mobilenet to finish loading then proceed with next stype
        ai_model = await mobilenet.load();

    })();

    // This function:
    // 1 Takes the uploaded image 
    // 2 Compares it with the quest description
    // 3 Decides whether the image is valid
    async function check_image(file_input, description) {
        // SAFETY 1: Check if model exists
        if (!ai_model) throw new Error("AI Model not loaded yet. Please wait.");

        // SAFETY 2: Convert Uploaded File to Image because MobileNet only works with images to prevent any error
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file_input.files[0]);
        
        // SAFETY 3: Wait for image to fully load 
        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = () => reject(new Error("Failed to load image data."));
        });

        console.log("Image loaded, running classify...")

        // 1. .classify() is a function provided by MobileNet that: Takes an image → Returns what objects the AI sees
        // 2. Each object will have className (What the ai thinks it sees), and probability (Confidence probability from 0-1)
        // 3. The output would be like: [{className: "Banana", probability: 0.1 }, {className: "Apple", probability: 0.5}]
        const predictions = await ai_model.classify(img);
        console.log("AI Predictions:", predictions);

        // Make description to lower case because JavaScript text comparison is case-sensitive
        const desc = description.toLowerCase();
        // Default is false
        let match_found = false;

        predictions.forEach(p => {
            // Like similar to the description, we also have to make the classNames lowercase too
            const raw = p.className.toLowerCase();

            // The AI may provide multiple description in one className: ["recycling bin, trash can, bin, can"]
            // This line splits them into individual keywords
            const words = raw.split(/[, ]+/); // Split by comma OR space

            // This checks each word from the AI label.
            words.forEach(word => {
                // Remove unwanted spaces
                const clean_word = word.trim();
                // match_found set to true for each word IF:
                // 1. It has meaningful keywords > 2 characters
                // 2. Matches with description
                if (clean_word.length > 2 && desc.includes(clean_word)) {
                    match_found = true;
                }
            });
        });

        // This runs ONLY if no keywords are matched
        if (!match_found) {
            let saw_string = predictions.map(p => p.className).join(" OR ");
            // Inform user what the AI saw 
            throw new Error("AI Mismatch. Saw: " + saw_string);
        }

        // If code reaches here it means:
        // - At least one keyword matched
        // - AI verification succeeded
        // - Form submission is allowed
        return true;
    }

    // Only when the HTML page document finishes loading, run this code
    document.addEventListener('DOMContentLoaded', () => {

    // Get the quest submission form (only exists when user is logged in)
    const form = document.getElementById('quest-form');

    if (form) {
        form.addEventListener('submit', async function(e) {

            // ------------------------------------------------------------
            // Skip AI verification for Weekly quests
            // Weekly quests are manually reviewed by moderators/admins
            // ------------------------------------------------------------
            if (currentQuestType === 'Weekly') return;

            // Stop default form submission until AI verification completes
            e.preventDefault(); 

            // Cache frequently used elements and values
            const btn = this.querySelector('button');
            const file_input = document.getElementById('evidence-input');
            const desc_text = document.getElementById('m-desc').innerText;
            const originalText = "Scan & Submit"; 

            // ------------------------------------------------------------
            // SAFETY CHECK:
            // Ensure the user has selected an image before scanning
            // ------------------------------------------------------------
            if (file_input.files.length === 0) {
                alert("Please select a file.");
                return;
            }

            // ------------------------------------------------------------
            // UI FEEDBACK:
            // Indicate that AI scanning is in progress
            // ------------------------------------------------------------
            btn.innerText = "Scanning...";
            btn.style.background = "#555";
            btn.disabled = true;

            try {
                // ========================================================
                // 1. RUN AI IMAGE VERIFICATION
                // ========================================================
                await check_image(file_input, desc_text);
                
                // ========================================================
                // 2. VISUAL SUCCESS FEEDBACK
                // ========================================================
                btn.innerText = "Verified!";
                btn.style.background = "#27ae60";

                // ========================================================
                // 3. USER CONFIRMATION ALERT
                // ========================================================
                alert(
                    "AI Verification Successful!\n\n" +
                    "Your evidence matches the description. Submitting your quest now..."
                );
                
                // ========================================================
                // 4. PASS AI RESULT TO SERVER
                // --------------------------------------------------------
                // Hidden input is appended so PHP knows this was AI-approved
                // ========================================================
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ai_verified';
                input.value = '1';
                this.appendChild(input);

                // ========================================================
                // 5. SUBMIT FORM AFTER SHORT DELAY
                // ========================================================
                setTimeout(() => { this.submit(); }, 500);

            } catch (error) {
                // ========================================================
                // AI VERIFICATION FAILURE HANDLING
                // ========================================================
                console.error(error);

                // Show detailed AI error message (e.g., detected objects)
                alert("Verification Failed\n\n" + error.message); 
                
                // Reset button UI so user can try again
                btn.innerText = originalText;
                btn.style.background = "#264633";
                btn.disabled = false;
            }
        });
    }
});


// MODAL CLOSE HANDLER (CLICK OUTSIDE TO CLOSE)
window.onclick = function(e) {
    if (e.target == document.getElementById('quest-modal')) close_modal();
}
</script>

<?php include '../includes/footer.php'; ?>