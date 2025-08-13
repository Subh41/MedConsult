<?php
session_start();

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

// Fetch doctors from database
$sql = "SELECT d.*, u.full_name as doctor_name FROM doctors d JOIN users u ON d.user_id = u.id";
$result = $conn->query($sql);
$doctors = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Filter doctors based on search criteria
$filteredDoctors = $doctors;

if (isset($_GET['specialization']) && !empty($_GET['specialization'])) {
    $filteredDoctors = array_filter($filteredDoctors, function($doctor) {
        return strtolower($doctor['specialization']) == strtolower($_GET['specialization']);
    });
}

if (isset($_GET['location']) && !empty($_GET['location'])) {
    // In a real implementation, you would filter by actual location
    // For now, we'll just show a message
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Doctors - MedConsult</title>
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
                    <?php $isLoggedIn = isset($_SESSION['user_id']); ?>
                    <?php if ($isLoggedIn): 
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
        <div class="container doctors-container">
            <h1 style="text-align: center; color: #2c5aa0; margin-bottom: 30px;">Find Doctors</h1>
            
            <div class="search-section">
                <h3>Search for Doctors</h3>
                <form class="search-form" method="GET" action="">
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <select id="specialization" name="specialization">
                            <option value="">All Specializations</option>
                            <option value="cardiologist" <?php echo (isset($_GET['specialization']) && $_GET['specialization'] == 'cardiologist') ? 'selected' : ''; ?>>Cardiologist</option>
                            <option value="dermatologist" <?php echo (isset($_GET['specialization']) && $_GET['specialization'] == 'dermatologist') ? 'selected' : ''; ?>>Dermatologist</option>
                            <option value="pediatrician" <?php echo (isset($_GET['specialization']) && $_GET['specialization'] == 'pediatrician') ? 'selected' : ''; ?>>Pediatrician</option>
                            <option value="neurologist" <?php echo (isset($_GET['specialization']) && $_GET['specialization'] == 'neurologist') ? 'selected' : ''; ?>>Neurologist</option>
                            <option value="orthopedic" <?php echo (isset($_GET['specialization']) && $_GET['specialization'] == 'orthopedic') ? 'selected' : ''; ?>>Orthopedic</option>
                            <option value="gynecologist" <?php echo (isset($_GET['specialization']) && $_GET['specialization'] == 'gynecologist') ? 'selected' : ''; ?>>Gynecologist</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <select id="location" name="location">
                            <option value="">All Locations</option>
                            <option value="downtown" <?php echo (isset($_GET['location']) && $_GET['location'] == 'downtown') ? 'selected' : ''; ?>>Downtown</option>
                            <option value="uptown" <?php echo (isset($_GET['location']) && $_GET['location'] == 'uptown') ? 'selected' : ''; ?>>Uptown</option>
                            <option value="suburbs" <?php echo (isset($_GET['location']) && $_GET['location'] == 'suburbs') ? 'selected' : ''; ?>>Suburbs</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Search</button>
                    </div>
                </form>
            </div>

            <div class="doctors-list">
                <?php if (empty($filteredDoctors)): ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>No doctors found</h3>
                        <p>Try adjusting your search criteria</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($filteredDoctors as $doctor): ?>
                        <div class="doctor-card">
                            <div class="doctor-avatar"><?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?></div>
                            <div class="doctor-info">
                                <h3><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                                <p><i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars(ucfirst($doctor['specialization'])); ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['clinic_address']); ?></p>
                                <p><i class="fas fa-star"></i> <?php echo number_format($doctor['rating'], 1); ?>/5 (<?php echo $doctor['experience_years']; ?> years experience)</p>
                                <p><i class="fas fa-dollar-sign"></i> Consultation Fee: $<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                                <p style="color: #27ae60; font-weight: bold;"><i class="fas fa-check-circle"></i> Available for booking</p>
                            </div>
                            <div class="doctor-actions">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="book-appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn-book">
                                        <i class="fas fa-calendar-plus"></i> Book Appointment
                                    </a>
                                <?php else: ?>
                                    <a href="/Medical consultation system/login-form.php" class="btn-book">
                                        <i class="fas fa-sign-in-alt"></i> Login to Book
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
