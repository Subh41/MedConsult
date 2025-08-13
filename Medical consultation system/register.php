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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $userType = 'patient'; // Force all registrations to be as patients
    
    // Validate input
    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Check if email already exists
        $checkEmail = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (full_name, email, phone, password, user_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $fullName, $email, $phone, $hashedPassword, $userType);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_type'] = $userType;
                
                // Redirect to dashboard or home page
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>Registration Result - MedConsult</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="form-container">
        <?php if (isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
            <a href="/Medical consultation system/register.php" class="btn-submit" style="text-decoration: none; text-align: center; display: block;">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>
