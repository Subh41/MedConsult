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
    <title>Doctor Registration - MedConsult</title>
    <link rel="stylesheet" href="/Medical consultation system/styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Doctor Registration</h2>
        <form action="/Medical consultation system/doctor-register.php" method="POST">
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
            <div class="form-group">
                <label for="specialization">Specialization:</label>
                <select id="specialization" name="specialization" required>
                    <option value="">Select Specialization</option>
                    <option value="Cardiologist">Cardiologist</option>
                    <option value="Dermatologist">Dermatologist</option>
                    <option value="Neurologist">Neurologist</option>
                    <option value="Pediatrician">Pediatrician</option>
                    <option value="Orthopedist">Orthopedist</option>
                    <option value="Gynecologist">Gynecologist</option>
                    <option value="Psychiatrist">Psychiatrist</option>
                    <option value="Ophthalmologist">Ophthalmologist</option>
                    <option value="ENT Specialist">ENT Specialist</option>
                </select>
            </div>
            <div class="form-group">
                <label for="experience">Years of Experience:</label>
                <input type="number" id="experience" name="experience" min="1" max="50" required>
            </div>
            <div class="form-group">
                <label for="clinicAddress">Clinic Address:</label>
                <textarea id="clinicAddress" name="clinicAddress" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="consultationFee">Consultation Fee ($):</label>
                <input type="number" id="consultationFee" name="consultationFee" min="0" step="0.01" required>
            </div>
            
            <h3>Availability</h3>
            <div id="availability-container">
                <div class="availability-item">
                    <select name="days[]" required>
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                    <input type="time" name="start_times[]" required>
                    <input type="time" name="end_times[]" required>
                    <button type="button" class="btn-remove" onclick="removeAvailability(this)">Remove</button>
                </div>
            </div>
            <button type="button" class="btn-add" onclick="addAvailability()">Add Another Time Slot</button>
            
            <button type="submit" class="btn-submit">Register as Doctor</button>
        </form>
        <div class="form-footer">
            <p>Already have an account? <a href="/Medical consultation system/login-form.php">Login</a></p>
            <p>Register as a patient instead? <a href="/Medical consultation system/register-form.php">Register as Patient</a></p>
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
        
        function addAvailability() {
            var container = document.getElementById('availability-container');
            var newItem = document.createElement('div');
            newItem.className = 'availability-item';
            newItem.innerHTML = `
                <select name="days[]" required>
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
                <input type="time" name="start_times[]" required>
                <input type="time" name="end_times[]" required>
                <button type="button" class="btn-remove" onclick="removeAvailability(this)">Remove</button>
            `;
            container.appendChild(newItem);
        }
        
        function removeAvailability(button) {
            if (document.querySelectorAll('.availability-item').length > 1) {
                button.parentElement.remove();
            } else {
                alert('You must have at least one availability slot.');
            }
        }
    </script>
</body>
</html>
