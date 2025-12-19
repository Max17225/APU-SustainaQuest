<?php
$title = 'Reset Password - SustainaQuest';
$left_paragraph = 'Every change begins with a choice — a choice to care, to act, and to move forward. SustainaQuest empowers you to take that first step — to make sustainability not a dream, but a habit. Together, our small steps become the path to a greener tomorrow.';

$form_content =<<<HTML
    <form action="reset_password_process.php" method="POST">
        <h1>Reset Password</h1>

        <!-- username -->
        <div class="input-box">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <svg width="24" height="24" viewBox="0 0 24 24"><path fill="#ffffffff" fill-rule="evenodd" d="M3 10.417c0-3.198 0-4.797.378-5.335c.377-.537 1.88-1.052 4.887-2.081l.573-.196C10.405 2.268 11.188 2 12 2s1.595.268 3.162.805l.573.196c3.007 1.029 4.51 1.544 4.887 2.081C21 5.62 21 7.22 21 10.417v1.574c0 5.638-4.239 8.375-6.899 9.536C13.38 21.842 13.02 22 12 22s-1.38-.158-2.101-.473C7.239 20.365 3 17.63 3 11.991zM14 9a2 2 0 1 1-4 0a2 2 0 0 1 4 0m-2 8c4 0 4-.895 4-2s-1.79-2-4-2s-4 .895-4 2s0 2 4 2" clip-rule="evenodd"/></svg>
        </div>

        <!-- email -->
        <div class="input-box">
            <input type="email" id="email" name="email" placeholder="Email Address" required>
            <svg width="24" height="24" viewBox="0 0 24 24"><path fill="#ffffffff" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2m0 2v.01L12 11L4 6.01V6h16M4 18V8l8 5l8-5v10H4z"/></svg>
        </div>

        <!-- new password -->
        <div class="input-box">
            <input type="password" name="new_password" placeholder="New Password" required>
            <svg width="24" height="24" viewBox="0 0 24 24" style="margin-bottom: 1px;"><path fill="#ffffffff" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h1V6a5 5 0 0 1 5-5a5 5 0 0 1 5 5v2zm-6-5a3 3 0 0 0-3 3v2h6V6a3 3 0 0 0-3-3"/></svg>
        </div>

        <!-- confirm password -->
        <div class="input-box">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <svg width="24" height="24" viewBox="0 0 24 24" style="margin-bottom: 1px;"><path fill="#ffffffff" d="M12 17a2 2 0 0 0 2-2a2 2 0 0 0-2-2a2 2 0 0 0-2 2a2 2 0 0 0 2 2m6-9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h1V6a5 5 0 0 1 5-5a5 5 0 0 1 5 5v2zm-6-5a3 3 0 0 0-3 3v2h6V6a3 3 0 0 0-3-3"/></svg>
        </div>       

        <!-- Submit Button -->
        <button type="submit" class="btn">Confirm</button>

    </form>
HTML;

$bottom_link = '<div class="back-to-login"><a href="login.php">Back to Login</a></div>';

include 'auth_layout.php';
?>