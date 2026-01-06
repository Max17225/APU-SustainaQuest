<!-- admin/js/admin_management.php -->
<!---- Management scirpt (quest_management, user_management, shop_management) ---->
<script>
    // Add New button
    document.getElementById('addBtn')?.addEventListener('click', () => {
        const params = new URLSearchParams(window.location.search);

        const module = params.get('module');
        const page   = params.get('page');

        window.location.href = `?module=${module}&page=${page}&action=create`;
    });
</script>

<script>
    // Script for edit button (located inside management table)
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

<script>
    // Script for Delete button
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

<script>
    // Script for select All btn
    document.getElementById('selectAll')?.addEventListener('change', e => {
        document.querySelectorAll('.row-check')
            .forEach(cb => cb.checked = e.target.checked);
    });
</script>

<script>
    // Script for management's top bar (search / sort tools / filter)
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
