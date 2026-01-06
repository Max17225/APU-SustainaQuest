<!-- admin/js/top_bar_timer.php -->
<script>
    function updateTopBarTime() {
        const now = new Date();

        document.getElementById('topBarDate').textContent =
            now.toLocaleDateString(undefined, {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

        document.getElementById('topBarTime').textContent =
            now.toLocaleTimeString(undefined, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
    }

    // initial render
    updateTopBarTime();

    // update every second
    setInterval(updateTopBarTime, 1000);
</script>