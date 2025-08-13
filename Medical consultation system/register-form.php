<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'doctor') {
        header("Location: /Medical consultation system/doctor-dashboard.php");
    } else {
        header("Location: /Medical consultation system/dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MedConsult</title>
    <link rel="stylesheet" href="/Medical consultation system/styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Register for MedConsult</h2>
        <form action="/Medical consultation system/register.php" method="POST">
            <div class="form-group">
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <span class="toggle-password" onclick="togglePassword()">Show</span>
                </div>
            </div>
            <button type="submit" class="btn-submit">Register</button>
        </form>
        <div class="form-footer">
            <p>Already have an account? <a href="/Medical consultation system/login-form.php">Login</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById('password');
            var toggleText = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleText.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleText.textContent = 'Show';
            }
        }
    </script>
</body>
</html>
