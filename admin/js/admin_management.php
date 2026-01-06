<!-------------------- Management scirpt (quest_management, user_management, shop_management) --------------------------------->

<!-- Only for shop_management, change the table head base on the filter -->
<script>
    document.querySelectorAll('.fil-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;

            document.querySelector('.status-permanent').style.display =
                type === 'permanent' ? 'inline' : 'none';

            document.querySelector('.status-limited').style.display =
                type === 'limited' ? 'inline' : 'none';
        });
    });
</script>

<!-- Add New button -->
<script>
    document.getElementById('addBtn')?.addEventListener('click', () => {
        const params = new URLSearchParams(window.location.search);

        const module = params.get('module');
        const page   = params.get('page');

        window.location.href = `?module=${module}&page=${page}&action=create`;
    });
</script>

<!-- Script for edit button (located inside management table) -->
<script>
    document.addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-btn');
        if (!editBtn) return;

        const params = new URLSearchParams(window.location.search);

        const module = params.get('module');
        const page   = params.get('page');

        const row = editBtn.closest('tr');
        if (!row) return;

        const id = row.dataset.id;
        if (!id) return;

        window.location.href = `?module=${module}&page=${page}&action=edit&id=${id}`;
    });
</script>

<!-- Script for Delete button -->
<script>
    document.getElementById('deleteBtn')?.addEventListener('click', () => {

        const params = new URLSearchParams(window.location.search);
        const module = params.get('module');
        const page   = params.get('page');

        let entity = null;

        if (module === 'user' && page === 'user') entity = 'user';
        if (module === 'user' && page === 'mod')  entity = 'mod';
        if (module === 'quest') entity = 'quest';
        if (module === 'shop')  entity = 'shop';

        if (!entity) {
            alert('Invalid delete target');
            return;
        }

        const ids = [...document.querySelectorAll('.row-check:checked')]
            .map(cb => cb.closest('tr').dataset.id);

        if (!ids.length) {
            alert('Select at least one row.');
            return;
        }

        // quest delete need reason
        let reason = null;
        if (entity === 'quest') {
            reason = prompt('Enter delete reason for selected quest(s):');
            if (!reason || !reason.trim()) {
                alert('Delete reason is required.');
                return;
            }
        } else {
            if (!confirm(`Delete ${ids.length} selected record(s)?`)) return;
        }


        fetch('process/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ entity, ids, reason })
        })
        .then(res => res.text())
        .then(text => {
            try {
                const json = JSON.parse(text);
                if (json.success) location.reload();
            } catch (e) {
                console.error('Server returned:', text);
                alert('Delete failed. Check console.');
            }
        });
    });
</script>

<!-- Script for select All btn -->
<script>
    document.getElementById('selectAll')?.addEventListener('change', e => {
        document.querySelectorAll('.row-check')
            .forEach(cb => cb.checked = e.target.checked);
    });
</script>

<!-- Script for management's top bar (search / sort tools / filter) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const tableBody = document.querySelector('.management-table .record-table tbody');
        if (!tableBody) return;

        const rows = Array.from(tableBody.querySelectorAll('tr'));

        let currentSort = null;

        /* =========================
        Current states
        ========================= */
        let currentBan       = '0';          // user
        let currentQuestType = 'daily';      // quest
        let currentItemType  = 'permanent';  // shop

        const currentModule = "<?= $_GET['module'] ?? '' ?>";
        const currentPage   = "<?= $_GET['page'] ?? '' ?>";

        /* =========================
        SEARCH
        ========================= */
        document.getElementById('searchInput')?.addEventListener('input', e => {
            const keyword = e.target.value.toLowerCase();

            rows.forEach(row => {
                let match = false;

                if (currentModule === 'user') {
                    match = (row.dataset.username || '').includes(keyword);
                }

                if (currentModule === 'quest') {
                    match =
                        (row.dataset.title || '').includes(keyword) ||
                        (row.dataset.questCreator || '').includes(keyword);
                }

                if (currentModule === 'shop') {
                    match = (row.dataset.name || '').includes(keyword);
                }

                row.style.display = match ? '' : 'none';
            });
        });

        /* =========================
        FILTER BUTTONS
        ========================= */
        document.querySelectorAll('.fil-btn').forEach(btn => {
            btn.addEventListener('click', () => {

                document.querySelectorAll('.fil-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (currentModule === 'user') { // ban or normal
                    currentBan = btn.dataset.ban;
                }

                if (currentModule === 'quest') { // daily or weekly
                    currentQuestType = btn.dataset.questType.toLowerCase();
                }

                if (currentModule === 'shop') {
                    currentItemType = btn.dataset.type.toLowerCase(); 
                    // permanent | limited
                }

                applyFilterAndSort();
            });
        });

        /* =========================
        SORT BUTTONS
        ========================= */
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {

                if (currentSort === btn.dataset.sort) {
                    currentSort = null;
                    btn.classList.remove('active');
                } else {
                    document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentSort = btn.dataset.sort;
                }

                applyFilterAndSort();
            });
        });

        /* =========================
        CORE LOGIC
        ========================= */
        function applyFilterAndSort() {

            let filtered = [...rows];

            /* ---------- FILTER ---------- */
            if (currentModule === 'user' && currentPage === 'user') {
                filtered = filtered.filter(row => row.dataset.banned === currentBan);
            }

            if (currentModule === 'quest') {
                filtered = filtered.filter(row => row.dataset.questType === currentQuestType);
            }

            if (currentModule === 'shop') {
                filtered = filtered.filter(row => row.dataset.type === currentItemType);
            }

            /* ---------- SORT ---------- */
            if (currentSort) {
                filtered.sort((a, b) => {

                    /* USER */
                    if (currentModule === 'user') {
                        switch (currentSort) {
                            case 'level':        return b.dataset.level - a.dataset.level;
                            case 'greenPoints':  return b.dataset.points - a.dataset.points;
                            case 'sub-approve':  return b.dataset.approved - a.dataset.approved;
                            case 'sub-reject':   return b.dataset.rejected - a.dataset.rejected;
                            case 'active':       return b.dataset.total - a.dataset.total;
                            case 'inactive':     return a.dataset.total - b.dataset.total;
                        }
                    }

                    /* QUEST */
                    if (currentModule === 'quest') {
                        switch (currentSort) {
                            case 'createDate':
                                return new Date(b.dataset.createDate) - new Date(a.dataset.createDate);
                            case 'exp':
                                return b.dataset.expReward - a.dataset.expReward;
                            case 'greenPoint':
                                return b.dataset.pointReward - a.dataset.pointReward;
                            case 'activated':
                                return b.dataset.isActive - a.dataset.isActive;
                        }
                    }

                    /* SHOP */
                    if (currentModule === 'shop') {
                        switch (currentSort) {
                            case 'total-redeem':
                                return b.dataset.total - a.dataset.total;
                            case 'total-quantity':
                                return b.dataset.quantity - a.dataset.quantity;
                            case 'point-cost':
                                return b.dataset.point - a.dataset.point;
                            case 'item-available-status':
                                return b.dataset.status - a.dataset.status;
                        }
                    }

                    return 0;
                });
            } else {
                /* DEFAULT SORT */
                if (currentModule === 'user') {
                    filtered.sort((a, b) =>
                        a.dataset.username.localeCompare(b.dataset.username)
                    );
                }

                if (currentModule === 'quest') {
                    filtered.sort((a, b) =>
                        a.dataset.title.localeCompare(b.dataset.title)
                    );
                }

                if (currentModule === 'shop') {
                    filtered.sort((a, b) =>
                        a.dataset.name.localeCompare(b.dataset.name)
                    );
                }
            }

            tableBody.innerHTML = '';
            filtered.forEach(row => tableBody.appendChild(row));
        }

        applyFilterAndSort();
    });
</script>

<!-- Script for form input (if invalid input give hint) -->
<script>
    document.addEventListener('input', async e => {
        const input = e.target;
        const type = input.dataset.validate; // data-validate (username, email...)
        if (!type) return;

        const form = input.closest('form');
        const mode = form.dataset.mode; //  data-mode form mode create / edit
        const hint = input.nextElementSibling; 
        const value = input.value.trim();

        // EDIT MODE: unchanged field -> skip validation
        if (mode === 'edit' &&
            input.dataset.original !== undefined && // if user didnt modify the data
            value === input.dataset.original) {

            input.classList.remove('valid', 'invalid');
            hint.textContent = '';
            checkForm();
            return;
        }

        if (!value) {
            input.classList.remove('valid', 'invalid');
            hint.textContent = '';
            checkForm();
            return;
        }

        if (type === 'password') {
            if (mode === 'edit' && value === '') {
                input.classList.remove('valid', 'invalid');
                hint.textContent = '';
            } else if (value.length < 8) {
                setInvalid(input, hint, 'Minimum 8 characters');
            } else {
                setValid(input, hint);
            }
            checkForm();
            return;
        }

        const entity = document.querySelector('input[name="entity"]').value;
        const id = document.querySelector('input[name="id"]')?.value ?? '';

        const res = await fetch(
            `/APU-SustainaQuest/admin/process/validate.php?type=${type}&entity=${entity}&id=${id}&value=${encodeURIComponent(value)}`
        );

        const data = await res.json();

        data.valid ? setValid(input, hint) : setInvalid(input, hint, data.message);
        checkForm();
    });

    // For the data without data-validate
    document.addEventListener('change', e => {
        const form = e.target.closest('form[data-mode="edit"]');
        if (!form) return;

        checkForm();
    });

    function setValid(input, hint) {
        input.classList.add('valid');
        input.classList.remove('invalid');
        hint.textContent = '';
    }

    function setInvalid(input, hint, msg) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        hint.textContent = msg;
    }

    function checkForm() {
        const form = document.querySelector('form[data-mode]');
        if (!form) return;
        
        const submit = form.querySelector('button[type="submit"]');
        const mode = form.dataset.mode; // edit or create


        let hasInvalid = false;
        let hasChange = false;

        form.querySelectorAll('input').forEach(input => {

            // invalid check
            if (input.classList.contains('invalid')) {
                hasInvalid = true;
            }

            //Check for Edit, check if they change any data or not, if yes enable update button to click
            if (mode !== 'edit') return;

            const original = input.dataset.original;

            // PASSWORD: any input enables update
            if (input.type === 'password') {
                if (input.value.trim() !== '') {
                    hasChange = true;
                }
                return;
            }

            // Radio
            if (input.type === 'radio') {
                if (input.checked && original !== undefined && input.value !== original) {
                    hasChange = true;
                }
                return;
            }

            // NORMAL INPUT (text / number / email)
            if (original !== undefined && input.value !== original) {
                hasChange = true;
            }

            // file
            if (input.type === 'file') {
                if (input.files && input.files.length > 0) {
                    hasChange = true;
                }
                return;
            }
        });

        /* =========================
            TEXTAREA (Check if they edit text area)
           ========================= */
        form.querySelectorAll('textarea').forEach(textarea => {
            if (mode !== 'edit') return;

            const original = textarea.dataset.original ?? '';

            if (textarea.value !== original) {
                hasChange = true;
            }
        });


        if (mode === 'create') {
            let empty = false;

            // Required inputs (text, number, email)
            form.querySelectorAll('input[required]:not([type="radio"]):not([type="file"])')
                .forEach(i => {
                    if (!i.value.trim()) empty = true;
                });

            // Required textarea
            form.querySelectorAll('textarea[required]')
                .forEach(t => {
                    if (!t.value.trim()) empty = true;
                });

            // Required file
            form.querySelectorAll('input[type="file"][required]')
                .forEach(f => {
                    if (!f.files || f.files.length === 0) empty = true;
                });

            // Required radio groups
            const radioNames = new Set(
                [...form.querySelectorAll('input[type="radio"][required]')].map(r => r.name)
            );

            radioNames.forEach(name => {
                if (!form.querySelector(`input[type="radio"][name="${name}"]:checked`)) {
                    empty = true;
                }
            });

            submit.disabled = hasInvalid || empty;
            return;
        }

        // for edit mode
        submit.disabled = !hasChange || hasInvalid;
    }

    document.addEventListener('DOMContentLoaded', checkForm);
    document.addEventListener('input', checkForm);
    document.addEventListener('change', checkForm);

</script>

<!-- Click Image to change -->
<script>
    const fileInput = document.getElementById('icon');
    const frame = document.getElementById('imageFrame');
    const preview = document.getElementById('previewImg');

    frame.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.hidden = false;

            const placeholder = frame.querySelector('.placeholder');
            if (placeholder) placeholder.remove();
        };
        reader.readAsDataURL(file);
    });
</script>

<!-- Shop management from only (Create)-->
<script>
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

<!-- Shop management from only (Edit) -->
<script>
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