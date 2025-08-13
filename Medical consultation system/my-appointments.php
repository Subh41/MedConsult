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

$user_id = $_SESSION['user_id'];

// Fetch user's appointments
$sql = "SELECT a.*, d.id as doctor_id, u.full_name as doctor_name, d.specialization 
        FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        JOIN users u ON d.user_id = u.id 
        WHERE a.patient_id = ? 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Handle appointment cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $appointment_id = intval($_POST['appointment_id']);
    
    // Verify that the appointment belongs to the user
    $check_sql = "SELECT id FROM appointments WHERE id = ? AND patient_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $check_result = $stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update appointment status to cancelled
        $update_sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $appointment_id);
        
        if ($stmt->execute()) {
            // Refresh the appointments list
            header("Location: my-appointments.php");
            exit();
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
    <title>My Appointments - MedConsult</title>
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
                    <?php 
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
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container" style="margin-top: 120px;">
            <h1 style="text-align: center; color: #2c5aa0; margin-bottom: 30px;">My Appointments</h1>
            
            <?php if (empty($appointments)): ?>
                <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center;">
                    <i class="fas fa-calendar" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>No appointments found</h3>
                    <p>You haven't booked any appointments yet.</p>
                    <a href="find-doctors.php" class="btn btn-primary" style="margin-top: 20px;">Book an Appointment</a>
                </div>
            <?php else: ?>
                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #2c5aa0; color: white;">
                                    <th style="padding: 15px; text-align: left;">Doctor</th>
                                    <th style="padding: 15px; text-align: left;">Specialization</th>
                                    <th style="padding: 15px; text-align: left;">Date</th>
                                    <th style="padding: 15px; text-align: left;">Time</th>
                                    <th style="padding: 15px; text-align: left;">Status</th>
                                    <th style="padding: 15px; text-align: left;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 15px;"><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                        <td style="padding: 15px;"><?php echo htmlspecialchars(ucfirst($appointment['specialization'])); ?></td>
                                        <td style="padding: 15px;"><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td style="padding: 15px;"><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td style="padding: 15px;">
                                            <?php 
                                            switch ($appointment['status']) {
                                                case 'scheduled':
                                                    echo '<span style="color: #27ae60; font-weight: bold;">Scheduled</span>';
                                                    break;
                                                case 'completed':
                                                    echo '<span style="color: #3498db; font-weight: bold;">Completed</span>';
                                                    break;
                                                case 'cancelled':
                                                    echo '<span style="color: #e74c3c; font-weight: bold;">Cancelled</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php if ($appointment['status'] == 'scheduled'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                    <button type="submit" name="cancel_appointment" class="btn" style="background: #e74c3c; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;" onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php elseif ($appointment['status'] == 'completed'): ?>
                                                <button class="btn" style="background: #3498db; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;" onclick="alert('This appointment has been completed.');">
                                                    <i class="fas fa-check"></i> Completed
                                                </button>
                                            <?php else: ?>
                                                <span style="color: #999;">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
