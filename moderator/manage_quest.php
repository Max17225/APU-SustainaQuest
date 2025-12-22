<?php
// FILE: manage_quest.php
// DESCRIPTION: Moderator interface for creating, managing, and deleting sustainability quests.
// REQUIREMENTS: Uses PHP, HTML5, CSS (external), JavaScript, and MySQL (via db_connection.php).

// 1. Database Connection: Now in '../includes/db_connect.php' (Note the new name: db_connect.php)
require_once '../includes/db_connect.php'; 

// Session checks and general helpers (moved from utilities/)
require_once '../includes/session_check.php';
require_once '../includes/functions.php';
require_once 'mod_functions.php';

// Ensure only a logged-in Moderator can access this page BEFORE sending output
require_role('moderator');

// Set header path and optional page-specific CSS, then include global header
$path = "../"; // assets are one level up from moderator/
$page_css = 'moderator.css';
require_once '../includes/header.php';

// --- Database Operations & Form Handling ---

// 1. Process Quest Creation (Modal: Create New Quest)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_quest') {
    // Collect and sanitize input data
    $quest_title = sanitize_input($_POST['quest_title']);
    $quest_description = sanitize_input($_POST['quest_description']);
    $quest_type = sanitize_input($_POST['quest_type']); // 'daily' or 'weekly'
    $green_points = (int)$_POST['green_points'];
    
    // Validate required fields
    if (empty($quest_title) || empty($quest_description) || $green_points <= 0) {
        $error_message = "All fields are required and points must be positive.";
    } else {
        // Enforce Business Rule: Only one active Weekly Quest allowed
        if ($quest_type === 'weekly' && is_weekly_quest_active($conn)) {
            $error_message = "Business Rule Violation: Only one 'weekly' quest can be active at a time. Deactivate the current one first.";
        } else {
            // Attempt to create the quest
            if (create_quest($conn, $quest_title, $quest_description, $quest_type, $green_points, $_SESSION['moderator_id'])) {
                $success_message = "Quest '{$quest_title}' created successfully.";
            } else {
                $error_message = "Failed to create quest due to a database error.";
            }
        }
    }
}

// 2. Process Quest Editing (Modal: Edit Quest)
// ... (Similar logic for handling the 'edit_quest' POST request) ...

// 3. Process Quest Deletion/Status Change (Action Column)
// ... (Logic for 'delete_quest' or 'toggle_status' GET requests) ...

// 4. Fetch All Quests for Display
$quests = fetch_all_quests($conn); // function defined in ../utilities/moderator_functions.php
// (Helper functions are provided by ../utilities/moderator_functions.php)


// --- HTML Structure (View) ---
?>

    <div class="container">
        <h1>Quest Management</h1>
        <p>Create and manage sustainability challenges</p>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="quest-controls">
            <button id="btn-create-quest" class="btn btn-primary">Create Quest</button>
        </div>

        <div class="quest-list-table">
            <h2>Quest Titles</h2>
            <table>
                <thead>
                    <tr>
                        <th>Quest Titles</th>
                        <th>Type</th>
                        <th>Points</th>
                        <th>Completions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($quests)): ?>
                        <?php foreach ($quests as $quest): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quest['title']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($quest['type'])); ?></td>
                                <td><?php echo htmlspecialchars($quest['pointReward']); ?></td>
                                <td><?php echo htmlspecialchars($quest['completions_count']); ?></td>
                                <td><?php echo $quest['isActive'] ? '<span class="status-active">Active</span>' : '<span class="status-inactive">Inactive</span>'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-edit" data-quest-id="<?php echo $quest['questId']; ?>">Edit</button>
                                    <button class="btn btn-sm btn-delete">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No quests found. Start by creating one!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-create-quest" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Create New Quest</h2>
            <form action="manage_quest.php" method="POST" id="form-create-quest">
                <input type="hidden" name="action" value="create_quest">
                
                <label for="create_quest_title">Quest Title*</label>
                <input type="text" id="create_quest_title" name="quest_title" required placeholder="e.g. Zero Waste Week">

                <label for="create_quest_description">Description*</label>
                <textarea id="create_quest_description" name="quest_description" required placeholder="Describe the requirements..."></textarea>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="create_quest_type">Quest Type*</label>
                        <select id="create_quest_type" name="quest_type" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="create_green_points">Green Points*</label>
                        <input type="number" id="create_green_points" name="green_points" required min="1" placeholder="e.g. 50">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-create-quest')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Quest</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-edit-quest" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Edit Quest</h2>
            <form action="manage_quest.php" method="POST" id="form-edit-quest">
                <input type="hidden" name="action" value="edit_quest">
                <input type="hidden" name="quest_id" id="edit_quest_id">
                
                <label for="edit_quest_title">Quest Title</label>
                <input type="text" id="edit_quest_title" name="quest_title" value="Zero Waste Week">

                <label for="edit_quest_description">Description</label>
                <textarea id="edit_quest_description" name="quest_description">Describe</textarea>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_quest_type">Quest Type</label>
                        <input type="text" id="edit_quest_type" name="quest_type" value="Weekly" disabled> </div>
                    <div class="form-group">
                        <label for="edit_green_points">Green Points</label>
                        <input type="number" id="edit_green_points" name="green_points" value="xx">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-quest')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Quest</button>
                </div>
            </form>
        </div>
    </div>


    <script src="<?php echo $path; ?>assets/js/modal_handling.js"></script>
    <script>
        // Example JavaScript for opening and closing modals (modal_handling.js)
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        document.getElementById('btn-create-quest').addEventListener('click', function() {
            openModal('modal-create-quest');
        });

        // Event listener for EDIT buttons (requires AJAX to fetch data before opening modal)
        // document.querySelectorAll('.btn-edit').forEach(button => {
        //     button.addEventListener('click', function() {
        //         const questId = this.dataset.questId;
        //         // AJAX call to fetch quest details by questId and populate modal-edit-quest fields
        //         openModal('modal-edit-quest');
        //     });
        // });
        
        // --- Core Business Logic Enforcement via JS ---
        document.getElementById('form-create-quest').addEventListener('submit', function(e) {
            const type = document.getElementById('create_quest_type').value;
            // The critical check for 'weekly' quest is done on the server side (PHP) 
            // to prevent race conditions and ensure data integrity (is_weekly_quest_active function).
            // A secondary JS check can be added here for faster feedback if necessary.
        });

    </script>

    <?php include_once '../includes/footer.php'; ?>