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

// Fetch doctor's appointments
$appointments_sql = "SELECT a.*, u.full_name as patient_name 
                    FROM appointments a 
                    JOIN users u ON a.patient_id = u.id 
                    WHERE a.doctor_id = ? 
                    ORDER BY a.appointment_date ASC, a.appointment_time ASC";
$stmt = $conn->prepare($appointments_sql);
$stmt->bind_param("i", $doctor['id']);
$stmt->execute();
$appointments_result = $stmt->get_result();
$appointments = [];
while ($row = $appointments_result->fetch_assoc()) {
    $appointments[] = $row;
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - MedConsult</title>
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
                    <li><a href="/Medical consultation system/doctor-dashboard.php">Doctor Dashboard</a></li>
                    <li><a href="/Medical consultation system/logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container" style="margin-top: 120px;">
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <h1 style="color: #2c5aa0; margin-bottom: 10px;">Welcome, Dr. <?php echo htmlspecialchars($doctor['full_name']); ?>!</h1>
                        <p style="color: #666;">Manage your appointments and profile</p>
                    </div>
                    <div>
                        <a href="edit-doctor-profile.php" class="btn btn-primary" style="background: #007bff; border-color: #007bff; margin-right: 10px;">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                        <a href="#" class="btn btn-primary" style="background: #28a745; border-color: #28a745;">
                            <i class="fas fa-user-md"></i> <?php echo htmlspecialchars(ucfirst($doctor['specialization'])); ?>
                        </a>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="feature-card" style="background: #e3f2fd; border-left: 4px solid #2196f3;">
                        <i class="fas fa-calendar-check" style="font-size: 2rem; color: #2196f3;"></i>
                        <h3>Upcoming Appointments</h3>
                        <p style="font-size: 2rem; font-weight: bold; color: #2196f3;">
                            <?php 
                            $upcoming_count = 0;
                            foreach ($appointments as $appointment) {
                                $appointment_datetime = new DateTime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                                $now = new DateTime();
                                if ($appointment_datetime >= $now && $appointment['status'] == 'scheduled') {
                                    $upcoming_count++;
                                }
                            }
                            echo $upcoming_count;
                            ?>
                        </p>
                    </div>
                    
                    <div class="feature-card" style="background: #e8f5e9; border-left: 4px solid #4caf50;">
                        <i class="fas fa-dollar-sign" style="font-size: 2rem; color: #4caf50;"></i>
                        <h3>Consultation Fee</h3>
                        <p style="font-size: 2rem; font-weight: bold; color: #4caf50;">$<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                    </div>
                    
                    <div class="feature-card" style="background: #fff3e0; border-left: 4px solid #ff9800;">
                        <i class="fas fa-star" style="font-size: 2rem; color: #ff9800;"></i>
                        <h3>Your Rating</h3>
                        <p style="font-size: 2rem; font-weight: bold; color: #ff9800;"><?php echo number_format($doctor['rating'], 1); ?>/5.0</p>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 30px;">
                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <h2 style="color: #2c5aa0; margin-bottom: 20px;"><i class="fas fa-calendar-alt"></i> Your Appointments</h2>
                    
                    <?php if (empty($appointments)): ?>
                        <p style="text-align: center; color: #666; padding: 20px;">No appointments scheduled yet.</p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Patient</th>
                                        <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Date & Time</th>
                                        <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): 
                                        $appointment_datetime = new DateTime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                                        $now = new DateTime();
                                        $is_upcoming = $appointment_datetime >= $now && $appointment['status'] == 'scheduled';
                                    ?>
                                        <tr style="border-bottom: 1px solid #dee2e6; <?php echo $is_upcoming ? '' : 'opacity: 0.6;'; ?>">
                                            <td style="padding: 15px;"><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                            <td style="padding: 15px;">
                                                <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?><br>
                                                <small><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></small>
                                            </td>
                                            <td style="padding: 15px;">
                                                <span style="padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; 
                                                    <?php 
                                                    switch ($appointment['status']) {
                                                        case 'scheduled':
                                                            echo 'background: #e3f2fd; color: #1976d2;';
                                                            break;
                                                        case 'completed':
                                                            echo 'background: #e8f5e9; color: #388e3c;';
                                                            break;
                                                        case 'cancelled':
                                                            echo 'background: #ffebee; color: #d32f2f;';
                                                            break;
                                                    }
                                                    ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 30px;">
                        <h2 style="color: #2c5aa0; margin-bottom: 20px;"><i class="fas fa-user-md"></i> Your Profile</h2>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($doctor['full_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                            </div>
                            <div>
                                <p><strong>Specialization:</strong> <?php echo htmlspecialchars(ucfirst($doctor['specialization'])); ?></p>
                                <p><strong>Experience:</strong> <?php echo $doctor['experience_years']; ?> years</p>
                                <p><strong>Consultation Fee:</strong> $<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                            <p><strong>Clinic Address:</strong></p>
                            <p><?php echo htmlspecialchars($doctor['clinic_address']); ?></p>
                        </div>
                    </div>

                    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <h2 style="color: #2c5aa0; margin-bottom: 20px;"><i class="fas fa-clock"></i> Your Availability</h2>
                        
                        <?php if (empty($availability)): ?>
                            <p style="text-align: center; color: #666; padding: 20px;">No availability information set.</p>
                        <?php else: ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                                <?php foreach ($availability as $slot): ?>
                                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center; background: #f8f9fa;">
                                        <strong><?php echo $slot['day_of_week']; ?></strong><br>
                                        <span style="font-size: 0.9rem; color: #666;">
                                            <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - 
                                            <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
