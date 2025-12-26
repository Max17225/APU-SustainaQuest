<!-- Auth page layout, this layout will be used for login, forgot password, and registration pages -->

<!-- Prepare status message HTML if exists -->
<?php
session_start();
$status_HTML = '';
if (isset($_SESSION['status_msg']) && isset($_SESSION['status_class'])) {

    $msg   = htmlspecialchars($_SESSION['status_msg']); // Convert special characters to HTML entities to prevent XSS execution
    $class = htmlspecialchars($_SESSION['status_class']); // 'status-error' and 'status-warning' ONLY

    $status_HTML = '
        <div class="status-box ' . $class . '" id="statusBox">
            ' . $msg . '
        </div>
    ';

    // Clear the status message after displaying it once, so it doesn't persist on page refresh
    unset($_SESSION['status_msg'], $_SESSION['status_class']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>

    <!-- Styles -->
    <link rel="stylesheet" href="auth_layout.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Aldrich&family=Press+Start+2P&display=swap" rel="stylesheet">

    <!-- Logo Image Path -->
    <?php
        include '../includes/functions.php';
    ?>

</head>

<body>

    <!-- Particle Background -->
    <div id="particles-js"></div>

    <!-- Left Section -->
    <section class="left-wrapper">
        <div class="logo-with-paragraph">

            <h1 class="brand">
                <img src="<?php echo image_path("logo"); ?>" alt="SustainaQuest Logo">
                <span>SustainaQuest</span>
            </h1>

            <p>
                <?= $left_paragraph ?>
            </p>

        </div>

    </section>

    <!-- Right Section (Form Box) -->
    <section class="right-wrapper">
        <?= $status_HTML ?>
        <?= $form_content ?>
        <?= $bottom_link ?>
    </section>

    <!----------------------------------------------------------------------------------------- Particles.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

    <script>
        const config = {
            particles: {
                number: { value: 60, density: { enable: true, value_area: 800 } },
                color: { value: "#7EB77F" },
                shape: { type: "circle" },
                opacity: { value: 0.6 },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 130,
                    color: "#7EB77F",
                    opacity: 0.25,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 1.6,
                    direction: "none",
                    random: false,
                    straight: false,
                    out_mode: "out",
                    bounce: false
                }
            },
            interactivity: {
                detect_on: "canvas",
                events: {
                    onhover: { enable: true, mode: "repulse" },
                    onclick: { enable: true, mode: "push" },
                    resize: true
                },
                modes: {
                    grab: { distance: 400, line_linked: { opacity: 0.5 } },
                    bubble: { distance: 400, size: 40, duration: 2, opacity: 0.8 },
                    repulse: { distance: 100 },
                    push: { particles_nb: 4 },
                    remove: { particles_nb: 2 }
                }
            },
            retina_detect: true
        };

        particlesJS("particles-js", config);

        window.addEventListener("load", () => {
            setTimeout(() => {
                const el = document.getElementById("particles-js");
                if (el) el.style.opacity = "1";
            }, 300);
        });
    </script>

    <!-- Prevent spaces in username input -->
    <script>
        document.getElementById("username").addEventListener("keydown", function (e) {
            if (e.key === " ") {
                e.preventDefault();
            }
        });
    </script>

    <!-- Status Box Show/Hide Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusBox = document.getElementById('statusBox');
            if (!statusBox) return;

            // Show the status box with a slight delay for better UX (Work with CSS -> status-box.show)
            setTimeout(() => {
                statusBox.classList.add('show');
            }, 100);

            // Hide the status box when user starts typing in any input field
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    statusBox.classList.remove('show');
                });
            });
        });
    </script>

</body>
</html>