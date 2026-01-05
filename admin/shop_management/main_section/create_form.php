<!-- admin/shop_management/main_section/create_form.php -->

<div class="management form">

    <h2>New Item</h2>

    <form method="POST"
          action="/APU-SustainaQuest/admin/process/create.php"
          data-mode="create"
          enctype="multipart/form-data"
          novalidate>

        <!-- tell process what to create -->
        <input type="hidden" name="entity" value="items">

        <!-- ======================
             Picture
             ====================== -->
        <div class="form-group">
            <label>Item Picture</label>
            <input
                type="file"
                name="itemPicture"
                accept="image/*"
                required
            >
        </div>

        <!-- ======================
             Item Name
             ====================== -->
        <div class="form-group">
            <label>Item Name</label>
            <div class="input-hint">
                <input
                    type="text"
                    name="itemName"
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
                name="itemDesc"
                rows="4"
                required
            ></textarea>
        </div>

        <!-- ======================
             Quantity
             ====================== -->
        <div class="form-group">
            <label>Quantity</label>
            <input
                type="number"
                name="quantity"
                min="0"
                value="0"
                required
            >
        </div>

        <!-- ======================
             Point Cost
             ====================== -->
        <div class="form-group">
            <label>Point Cost</label>
            <input
                type="number"
                name="pointCost"
                min="1"
                value="1"
                required
            >
        </div>

        <!-- ======================
             Item Type
             ====================== -->
        <div class="form-group">
            <label>Type</label>

            <div class="radio-hint-wrapper">

                <div class="status-toggle">
                    <label class="toggle-option">
                        <input
                            class="radio-btn"
                            type="radio"
                            name="itemType"
                            value="Permanent"
                            required
                        >
                        <span>Permanent</span>
                    </label>

                    <label class="toggle-option">
                        <input
                            class="radio-btn"
                            type="radio"
                            name="itemType"
                            value="Limited"
                            required
                        >
                        <span>Limited</span>
                    </label>
                </div>

                <small class="hint" id="itemTypeHint"></small>

            </div>
        </div>

        <!-- ======================
             ACTIONS
             ====================== -->
        <div class="form-action">
            <button type="submit" class="btn" disabled>
                Create
            </button>

            <a href="?module=shop" class="btn">
                Cancel
            </a>
        </div>

    </form>
</div>

<!-- Script for shop management from only -->
<script>
    document.addEventListener('DOMContentLoaded', async () => {

        const form = document.querySelector('form[data-mode="create"]');
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        const hint = document.getElementById('itemTypeHint');

        let permanentCount = 0;

        /* =========================
        Fetch permanent item count
        ========================= */
        try {
            const res = await fetch('/APU-SustainaQuest/admin/shop_management/process/get_item_count.php?type=Permanent');
            const data = await res.json();
            permanentCount = data.count ?? 0;
        } catch {
            permanentCount = 0;
        }

        /* =========================
        Type change check
        ========================= */
        form.querySelectorAll('input[name="itemType"]').forEach(radio => {
            radio.addEventListener('change', () => {

                if (radio.value === 'Permanent' && permanentCount >= 8) {
                    hint.textContent = 'Already have 8 Permanent Item';
                    submitBtn.disabled = true;
                } else {
                    hint.textContent = '';
                    checkForm(); // script from layout.php
                }
            });
        });

    });
</script>
