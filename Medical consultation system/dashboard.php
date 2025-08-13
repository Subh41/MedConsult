<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Medical consultation system/login.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userType = $_SESSION['user_type'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MedConsult</title>
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
            <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
                <p style="color: #666; margin-bottom: 30px;">You are logged in as: <strong><?php echo ucfirst($userType); ?></strong></p>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-user-md"></i>
                        <h3>Find Doctors</h3>
                        <p>Search and book appointments with qualified doctors</p>
                        <a href="find-doctors.php" class="btn btn-primary">Browse Doctors</a>
                    </div>
                    
                    <div class="feature-card">
                        <i class="fas fa-robot"></i>
                        <h3>AI Assistant</h3>
                        <p>Get instant help with medical queries and doctor information</p>
                        <a href="chat.php" class="btn btn-primary">Start Chat</a>
                    </div>
                    
                    <div class="feature-card">
                        <i class="fas fa-calendar-check"></i>
                        <h3>My Appointments</h3>
                        <p>View and manage your upcoming appointments</p>
                        <a href="my-appointments.php" class="btn btn-primary">View Appointments</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
