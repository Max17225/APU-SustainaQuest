<?php
$title = 'Login - SustainaQuest';
$left_paragraph = 'Every change begins with a choice — a choice to care, to act, and to move forward. SustainaQuest empowers you to take that first step — to make sustainability not a dream, but a habit. Together, our small steps become the path to a greener tomorrow.';

$form_content = <<<HTML
    <form action="login_process.php" method="POST">

        <h1>Login</h1>

        <!-- Username -->
        <div class="input-box">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <svg width="24" height="24" viewBox="0 0 24 24"><path fill="#ffffffff" fill-rule="evenodd" d="M3 10.417c0-3.198 0-4.797.378-5.335c.377-.537 1.88-1.052 4.887-2.081l.573-.196C10.405 2.268 11.188 2 12 2s1.595.268 3.162.805l.573.196c3.007 1.029 4.51 1.544 4.887 2.081C21 5.62 21 7.22 21 10.417v1.574c0 5.638-4.239 8.375-6.899 9.536C13.38 21.842 13.02 22 12 22s-1.38-.158-2.101-.473C7.239 20.365 3 17.63 3 11.991zM14 9a2 2 0 1 1-4 0a2 2 0 0 1 4 0m-2 8c4 0 4-.895 4-2s-1.79-2-4-2s-4 .895-4 2s0 2 4 2" clip-rule="evenodd"/></svg>
        </div>

        <!-- Password -->
        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
            <svg width="24" height="24" viewBox="0 0 24 24" style="margin-bottom: 1px;"><path fill="#ffffffff" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h1V6a5 5 0 0 1 5-5a5 5 0 0 1 5 5v2zm-6-5a3 3 0 0 0-3 3v2h6V6a3 3 0 0 0-3-3"/></svg>
        </div>

        <!-- Remember / Forgot -->
        <div class="remember-forgot">
            <!-- remember me check box(This won't be implemented) -->
            <label>
                <input type="checkbox" name="remember_me" disabled> 
                <span>Remember me</span>
            </label>

            <!-- Forgot Password Link -->
            <a href="reset_password.php">Forgot Password</a>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn">Login</button>

        <!-- Register Link -->
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>

    </form>
HTML;

$bottom_link = '<div class="switch-btn"><a href="../index.php">View as Guest</a></div>';

include 'auth_layout.php';
?>