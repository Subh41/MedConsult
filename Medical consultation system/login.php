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
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    } else {
        // Check user credentials
        $sql = "SELECT id, full_name, email, password, user_type FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Set remember me cookie if checked
                if ($remember) {
                    setcookie('remember_user', $user['id'], time() + (86400 * 30), "/"); // 30 days
                }
                
                // Update last login
                $updateLogin = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateLogin);
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                
                // Redirect based on user type
                if ($user['user_type'] === 'doctor') {
                    header("Location: doctor-dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with this email!";
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
    <title>Login Result - MedConsult</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="form-container">
        <?php if (isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
            <a href="/Medical consultation system/login.php" class="btn-submit" style="text-decoration: none; text-align: center; display: block;">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>
