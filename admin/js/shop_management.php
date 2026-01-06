<!-- admin/js/shop_management.php -->

<script>
    // Only for shop_management, change the table head base on the filter
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