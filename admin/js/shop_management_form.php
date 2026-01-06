<!-- admin/js/shop_management_form.php -->

<script>
    // Shop management from only (Create)
    document.addEventListener('DOMContentLoaded', async () => {

        const form = document.querySelector('form[data-mode="create"]');
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        const hint = document.getElementById('itemTypeHint');
        const radios = form.querySelectorAll('input[name="itemType"]');

        let permanentCount = 0;

        // fetch total count
        try {
            const res = await fetch(
                '/APU-SustainaQuest/admin/shop_management/process/get_item_count.php?type=Permanent'
            );
            const data = await res.json();
            permanentCount = Number(data.count) || 0;
        } catch {
            permanentCount = 0;
        }

        // type selection logic
        radios.forEach(radio => {
            radio.addEventListener('change', () => {

                // Reset state
                hint.textContent = '';
                submitBtn.disabled = false;

                if (radio.value === 'Permanent' && permanentCount >= 8) {
                    hint.textContent = 'Maximum 8 Permanent items allowed.';
                    submitBtn.disabled = true;

                    // Auto-uncheck
                    radio.checked = false;
                }
            });
        });

    });
</script>

<script>
    // Shop management from only (Edit)
    document.addEventListener('change', async e => {

        const input = e.target;
        if (input.name !== 'availableStatus' || input.value !== '1') return;

        const hint = document.getElementById('limitHint');
        const submit = document.querySelector('button[type="submit"]');

        const res = await fetch('/APU-SustainaQuest/admin/shop_management/process/check_limited_available.php');
        const data = await res.json();

        if (!data.allowed) {
            hint.textContent = 'Maximum 8 limited items can be available.';
            submit.disabled = true;
            input.checked = false;
        } else {
            hint.textContent = '';
        }
    });
</script>