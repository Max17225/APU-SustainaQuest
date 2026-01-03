<!-- admin/quest_management/main_section/create_form.php -->

<div class="management form">

    <h2>New Quest</h2>

    <form method="POST" 
        action="/APU-SustainaQuest/admin/process/create.php" 
        data-mode="create"  
        enctype="multipart/form-data" 
        novalidate>

        <!-- tell process what to create -->
        <input type="hidden" name="entity" value="quest">

        <!-- ======================
             Picture
             ====================== -->
        <div class="form-group">
            <label>Quest Icon</label>
            <input
                type="file"
                name="questIcon"
                accept="image/*"
                required
            >
        </div>

        <!-- ======================
             Title
             ====================== -->
        <div class="form-group">
            <label>Title</label>
            <div class="input-hint">
                <input
                    type="text"
                    name="title"
                    data-validate="questTitle"
                    required
                >
                <small class="hint"></small>
            </div>
        </div>

        <!-- ======================
             Description
             ====================== -->
        <div class="form-group">
            <label>Description</label>
            <textarea
                name="description"
                rows="4"
                required
            ></textarea>
        </div>

        <!-- ======================
             Point Reward
             ====================== -->
        <div class="form-group">
            <label>Point Reward</label>
            <input type="number" name="pointReward" min="1" value="1" required>
        </div>

        <!-- ======================
             EXP Reward
             ====================== -->
        <div class="form-group">
            <label>EXP Reward</label>
            <input type="number" name="expReward" min="1" value="1" required>
        </div>

        <!-- ======================
             Quest Type
             ====================== -->
        <div class="form-group">
            <label>Type</label>
            <div class="status-toggle">
                <label class="toggle-option">
                    <input class="radio-btn" type="radio" name="questType" value="Daily" required>
                    <span>Daily</span>
                </label>
                <label class="toggle-option">
                    <input class="radio-btn" type="radio" name="questType" value="Weekly" required>
                    <span>Weekly</span>
                </label>
            </div>
        </div>

        <!-- ======================
             ACTIONS
             ====================== -->
        <div class="form-action">
            <button type="submit" class="btn" disabled>
                Create
            </button>

            <a href="?module=quest&page=available" class="btn">
                Cancel
            </a>
        </div>

    </form>
</div>
