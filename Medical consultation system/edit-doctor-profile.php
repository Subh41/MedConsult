<?php
session_start();

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: /Medical consultation system/login.php");
    exit();
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "medconsult_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch doctor profile
$doctor_sql = "SELECT d.*, u.full_name, u.email, u.phone FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.user_id = ?";
$stmt = $conn->prepare($doctor_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor_result = $stmt->get_result();
$doctor = $doctor_result->fetch_assoc();

if (!$doctor) {
    die("Doctor profile not found!");
}

// Fetch doctor's availability
$availability_sql = "SELECT * FROM doctor_availability WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$stmt = $conn->prepare($availability_sql);
$stmt->bind_param("i", $doctor['id']);
$stmt->execute();
$availability_result = $stmt->get_result();
$availability = [];
while ($row = $availability_result->fetch_assoc()) {
    $availability[] = $row;
}

// Days of the week for the form
$days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Initialize variables for form values
$fullName = $doctor['full_name'];
$email = $doctor['email'];
$phone = $doctor['phone'];
$specialization = $doctor['specialization'];
$experience = $doctor['experience_years'];
$clinicAddress = $doctor['clinic_address'];
$consultationFee = $doctor['consultation_fee'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $clinicAddress = $_POST['clinicAddress'];
    $consultationFee = $_POST['consultationFee'];
    $days = $_POST['days'] ?? [];
    $startTimes = $_POST['start_times'] ?? [];
    $endTimes = $_POST['end_times'] ?? [];
    
    // Validate input
    if (empty($fullName) || empty($email) || empty($phone) || 
        empty($specialization) || empty($experience) || empty($clinicAddress) || 
        empty($consultationFee)) {
        $error = "All fields are required!";
    } else {
        // Check if email already exists (excluding current user)
        $checkEmail = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Update user information
            $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $fullName, $email, $phone, $user_id);
            
            if ($stmt->execute()) {
                // Update doctor profile
                $sql = "UPDATE doctors SET specialization = ?, experience_years = ?, clinic_address = ?, consultation_fee = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sisdi", $specialization, $experience, $clinicAddress, $consultationFee, $user_id);
                
                if ($stmt->execute()) {
                    // Delete existing availability
                    $sql = "DELETE FROM doctor_availability WHERE doctor_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $doctor['id']);
                    $stmt->execute();
                    
                    // Insert new availability
                    $success = true;
                    for ($i = 0; $i < count($days); $i++) {
                        if (!empty($days[$i]) && !empty($startTimes[$i]) && !empty($endTimes[$i])) {
                            $sql = "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isss", $doctor['id'], $days[$i], $startTimes[$i], $endTimes[$i]);
                            
                            if (!$stmt->execute()) {
                                $success = false;
                                break;
                            }
                        }
                    }
                    
                    if ($success) {
                        $success_message = "Profile updated successfully!";
                        
                        // Refresh doctor data
                        $stmt = $conn->prepare($doctor_sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $doctor_result = $stmt->get_result();
                        $doctor = $doctor_result->fetch_assoc();
                        
                        // Refresh availability data
                        $stmt = $conn->prepare($availability_sql);
                        $stmt->bind_param("i", $doctor['id']);
                        $stmt->execute();
                        $availability_result = $stmt->get_result();
                        $availability = [];
                        while ($row = $availability_result->fetch_assoc()) {
                            $availability[] = $row;
                        }
                    } else {
                        $error = "Failed to update availability. Please try again.";
                    }
                } else {
                    $error = "Failed to update profile. Please try again.";
                }
            } else {
                $error = "Failed to update user information. Please try again.";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - MedConsult</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .availability-slots {
            margin-top: 10px;
        }
        
        .availability-slot {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .availability-slot select, 
        .availability-slot input {
            flex: 1;
        }
        
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-remove:hover {
            background: #c82333;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-add:hover {
            background: #218838;
        }
        
        .btn-submit {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            background: #0056b3;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-cancel:hover {
            background: #545b62;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section h2 {
            color: #2c5aa0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MedConsult</span>
                </div>
                <ul class="nav-menu">
                    <li><a href="/Medical consultation system/doctor-dashboard.php">Doctor Dashboard</a></li>
                    <li><a href="/Medical consultation system/logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="form-container">
            <h1><i class="fas fa-user-edit"></i> Edit Your Profile</h1>
            <p>Update your personal and professional information</p>
            
            <?php if (isset($error)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>Error:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>Success:</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="edit-doctor-profile.php">
                <div class="form-section">
                    <h2><i class="fas fa-user"></i> Personal Information</h2>
                    
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-stethoscope"></i> Professional Information</h2>
                    
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <select id="specialization" name="specialization" required>
                            <option value="">Select Specialization</option>
                            <option value="cardiology" <?php echo ($specialization == 'cardiology') ? 'selected' : ''; ?>>Cardiology</option>
                            <option value="dermatology" <?php echo ($specialization == 'dermatology') ? 'selected' : ''; ?>>Dermatology</option>
                            <option value="neurology" <?php echo ($specialization == 'neurology') ? 'selected' : ''; ?>>Neurology</option>
                            <option value="orthopedics" <?php echo ($specialization == 'orthopedics') ? 'selected' : ''; ?>>Orthopedics</option>
                            <option value="pediatrics" <?php echo ($specialization == 'pediatrics') ? 'selected' : ''; ?>>Pediatrics</option>
                            <option value="psychiatry" <?php echo ($specialization == 'psychiatry') ? 'selected' : ''; ?>>Psychiatry</option>
                            <option value="radiology" <?php echo ($specialization == 'radiology') ? 'selected' : ''; ?>>Radiology</option>
                            <option value="surgery" <?php echo ($specialization == 'surgery') ? 'selected' : ''; ?>>Surgery</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Years of Experience</label>
                        <input type="number" id="experience" name="experience" min="0" max="50" value="<?php echo $experience; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="clinicAddress">Clinic Address</label>
                        <textarea id="clinicAddress" name="clinicAddress" rows="3" required><?php echo htmlspecialchars($clinicAddress); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="consultationFee">Consultation Fee ($)</label>
                        <input type="number" id="consultationFee" name="consultationFee" min="0" step="0.01" value="<?php echo $consultationFee; ?>" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2><i class="fas fa-clock"></i> Availability</h2>
                    <p>Add your weekly availability for appointments</p>
                    
                    <div class="availability-slots" id="availabilitySlots">
                        <?php if (!empty($availability)): ?>
                            <?php foreach ($availability as $index => $slot): ?>
                                <div class="availability-slot">
                                    <select name="days[]" required>
                                        <option value="">Select Day</option>
                                        <?php foreach ($days_of_week as $day): ?>
                                            <option value="<?php echo $day; ?>" <?php echo ($slot['day_of_week'] == $day) ? 'selected' : ''; ?>><?php echo $day; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="time" name="start_times[]" value="<?php echo $slot['start_time']; ?>" required>
                                    <input type="time" name="end_times[]" value="<?php echo $slot['end_time']; ?>" required>
                                    <button type="button" class="btn-remove" onclick="removeSlot(this)">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="availability-slot">
                                <select name="days[]" required>
                                    <option value="">Select Day</option>
                                    <?php foreach ($days_of_week as $day): ?>
                                        <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="time" name="start_times[]" required>
                                <input type="time" name="end_times[]" required>
                                <button type="button" class="btn-remove" onclick="removeSlot(this)">Remove</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="btn-add" onclick="addSlot()">Add Another Time Slot</button>
                </div>
                
                <button type="submit" class="btn-submit">Update Profile</button>
                <a href="doctor-dashboard.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </main>
    
    <script>
        function addSlot() {
            const container = document.getElementById('availabilitySlots');
            const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            const slotDiv = document.createElement('div');
            slotDiv.className = 'availability-slot';
            
            let dayOptions = '<option value="">Select Day</option>';
            daysOfWeek.forEach(day => {
                dayOptions += `<option value="${day}">${day}</option>`;
            });
            
            slotDiv.innerHTML = `
                <select name="days[]" required>
                    ${dayOptions}
                </select>
                <input type="time" name="start_times[]" required>
                <input type="time" name="end_times[]" required>
                <button type="button" class="btn-remove" onclick="removeSlot(this)">Remove</button>
            `;
            
            container.appendChild(slotDiv);
        }
        
        function removeSlot(button) {
            if (document.querySelectorAll('.availability-slot').length > 1) {
                button.parentElement.remove();
            } else {
                alert('You need at least one availability slot.');
            }
        }
    </script>
</body>
</html>
