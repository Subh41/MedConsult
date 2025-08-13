<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConsult - Your Health Partner</title>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php $userType = $_SESSION['user_type'] ?? 'patient'; ?>
                        <?php if ($userType === 'doctor'): ?>
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
        <section class="hero">
            <div class="hero-content">
                <h1>Your Health, Our Priority</h1>
                <p>Connect with qualified doctors and get instant medical consultation</p>
                <div class="hero-buttons">
                    <a href="find-doctors.php" class="btn btn-primary">Find Doctors</a>
                    <a href="chat.php" class="btn btn-secondary">Chat with AI</a>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>Our Services</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-user-md"></i>
                        <h3>Find Doctors</h3>
                        <p>Search and connect with qualified doctors in your area</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-robot"></i>
                        <h3>AI Assistant</h3>
                        <p>Get instant answers about doctor availability and timings</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-calendar-check"></i>
                        <h3>Easy Booking</h3>
                        <p>Book appointments with your preferred doctors</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 MedConsult. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
