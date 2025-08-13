<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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

// Get doctor ID from URL parameter
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// Fetch doctor details
$doctor_sql = "SELECT d.*, u.full_name as doctor_name FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?";
$stmt = $conn->prepare($doctor_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor_result = $stmt->get_result();
$doctor = $doctor_result->fetch_assoc();

if (!$doctor) {
    die("Doctor not found!");
}

// Fetch doctor availability
$availability_sql = "SELECT * FROM doctor_availability WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$stmt = $conn->prepare($availability_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$availability_result = $stmt->get_result();
$availability = [];
while ($row = $availability_result->fetch_assoc()) {
    $availability[] = $row;
}

// Handle booking submission
$booking_success = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $patient_id = $_SESSION['user_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    
    // Validate input
    if (empty($appointment_date) || empty($appointment_time)) {
        $error_message = "Please select both date and time for your appointment.";
    } else {
        // Check if appointment slot is available
        $check_sql = "SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
        $stmt->execute();
        $check_result = $stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "This time slot is already booked. Please choose another time.";
        } else {
            // Insert appointment
            $insert_sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'scheduled')";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);
            
            if ($stmt->execute()) {
                $booking_success = true;
            } else {
                $error_message = "Failed to book appointment. Please try again.";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - MedConsult</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <?php if (isset($_SESSION['user_id'])): 
                        $userType = $_SESSION['user_type'] ?? 'patient';
                        if ($userType === 'doctor'): ?>
                            <li><a href="/Medical consultation system/doctor-dashboard.php">Doctor Dashboard</a></li>
                            <li><a href="/Medical consultation system/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="/Medical consultation system/index.php">Home</a></li>
                            <li><a href="/Medical consultation system/find-doctors.php">Find Doctors</a></li>
                            <li><a href="/Medical consultation system/chat.php">AI Chat</a></li>
                            <li><a href="/Medical consultation system/dashboard.php">Dashboard</a></li>
                            <li><a href="/Medical consultation system/my-appointments.php">My Appointments</a></li>
                            <li><a href="/Medical consultation system/logout.php">Logout</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="/Medical consultation system/index.php">Home</a></li>
                        <li><a href="/Medical consultation system/find-doctors.php">Find Doctors</a></li>
                        <li><a href="/Medical consultation system/chat.php">AI Chat</a></li>
                        <li><a href="/Medical consultation system/login-form.php">Login</a></li>
                        <li class="dropdown">
                            <a href="#" class="btn-register">Register <i class="fas fa-caret-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="/Medical consultation system/register-form.php">Register as Patient</a></li>
                                <li><a href="/Medical consultation system/doctor-register-form.php">Register as Doctor</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container" style="margin-top: 120px;">
            <?php if ($booking_success): ?>
                <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin-bottom: 30px; text-align: center;">
                    <h2><i class="fas fa-check-circle"></i> Appointment Booked Successfully!</h2>
                    <p>Your appointment with Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?> has been scheduled.</p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($_POST['appointment_date']); ?> | 
                    <strong>Time:</strong> <?php echo htmlspecialchars($_POST['appointment_time']); ?></p>
                    <p>You will receive a confirmation email shortly.</p>
                    <a href="dashboard.php" class="btn btn-primary" style="margin-top: 20px;">Back to Dashboard</a>
                </div>
            <?php else: ?>
                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px;">
                    <h2 style="color: #2c5aa0; margin-bottom: 20px;"><i class="fas fa-calendar-plus"></i> Book Appointment</h2>
                    
                    <?php if ($error_message): ?>
                        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="doctor-card" style="margin-bottom: 30px;">
                        <div class="doctor-avatar"><?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?></div>
                        <div class="doctor-info">
                            <h3><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                            <p><i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars(ucfirst($doctor['specialization'])); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['clinic_address']); ?></p>
                            <p><i class="fas fa-star"></i> <?php echo number_format($doctor['rating'], 1); ?>/5 (<?php echo $doctor['experience_years']; ?> years experience)</p>
                            <p><i class="fas fa-dollar-sign"></i> Consultation Fee: $<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                        
                        <div class="form-group">
                            <label for="appointment_date">Select Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="appointment_time">Select Time</label>
                            <select id="appointment_time" name="appointment_time" required>
                                <option value="">Choose a time slot</option>
                                <!-- Time slots will be populated based on doctor availability -->
                                <?php
                                // Generate time slots based on doctor availability
                                $today = date('l');
                                $today_availability = array_filter($availability, function($slot) use ($today) {
                                    return $slot['day_of_week'] == $today;
                                });
                                
                                if (!empty($today_availability)) {
                                    $slot = reset($today_availability);
                                    $start_time = strtotime($slot['start_time']);
                                    $end_time = strtotime($slot['end_time']);
                                    
                                    // Generate 30-minute slots
                                    for ($time = $start_time; $time < $end_time; $time += 1800) {
                                        $time_formatted = date('H:i:s', $time);
                                        echo '<option value="' . $time_formatted . '">' . date('g:i A', $time) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="book_appointment" class="btn-submit">
                            <i class="fas fa-calendar-check"></i> Confirm Booking
                        </button>
                    </form>
                </div>
                
                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <h3 style="color: #2c5aa0; margin-bottom: 20px;"><i class="fas fa-clock"></i> Doctor Availability</h3>
                    
                    <?php if (!empty($availability)): ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <?php foreach ($availability as $slot): ?>
                                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center;">
                                    <strong><?php echo $slot['day_of_week']; ?></strong><br>
                                    <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No availability information available for this doctor.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        // Set min date to today
        document.getElementById('appointment_date').min = new Date().toISOString().split('T')[0];
        
        // Update time slots when date changes
        document.getElementById('appointment_date').addEventListener('change', function() {
            const selectedDate = this.value;
            const timeSelect = document.getElementById('appointment_time');
            
            if (selectedDate) {
                // Enable the time select
                timeSelect.disabled = false;
                
                // Clear existing options except the first one
                timeSelect.innerHTML = '<option value="">Choose a time slot</option>';
                
                // Get the day of week from selected date
                const dateObj = new Date(selectedDate);
                const dayOfWeek = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
                
                // Find availability for this day
                const availability = <?php echo json_encode($availability); ?>;
                const dayAvailability = availability.find(slot => slot.day_of_week === dayOfWeek);
                
                if (dayAvailability) {
                    // Generate time slots based on doctor availability
                    const start = new Date(`1970-01-01T${dayAvailability.start_time}`);
                    const end = new Date(`1970-01-01T${dayAvailability.end_time}`);
                    
                    // Generate 30-minute slots
                    for (let time = new Date(start); time < end; time.setMinutes(time.getMinutes() + 30)) {
                        const timeValue = time.toTimeString().substring(0, 8);
                        const timeDisplay = time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        const option = document.createElement('option');
                        option.value = timeValue;
                        option.textContent = timeDisplay;
                        timeSelect.appendChild(option);
                    }
                }
            } else {
                timeSelect.disabled = true;
            }
        });
    </script>
</body>
</html>
