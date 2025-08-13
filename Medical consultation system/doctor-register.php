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
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $clinicAddress = $_POST['clinicAddress'];
    $consultationFee = $_POST['consultationFee'];
    $days = $_POST['days'];
    $startTimes = $_POST['start_times'];
    $endTimes = $_POST['end_times'];
    
    // Validate input
    if (empty($fullName) || empty($email) || empty($phone) || empty($password) || 
        empty($specialization) || empty($experience) || empty($clinicAddress) || 
        empty($consultationFee) || empty($days) || empty($startTimes) || empty($endTimes)) {
        $error = "All fields are required!";
    } else if (count($days) != count($startTimes) || count($days) != count($endTimes)) {
        $error = "Invalid availability data!";
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
            
            // Insert new user as doctor
            $userType = 'doctor';
            $sql = "INSERT INTO users (full_name, email, phone, password, user_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $fullName, $email, $phone, $hashedPassword, $userType);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // Insert doctor profile
                $sql = "INSERT INTO doctors (user_id, specialization, experience_years, clinic_address, consultation_fee) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isisd", $user_id, $specialization, $experience, $clinicAddress, $consultationFee);
                
                if ($stmt->execute()) {
                    $doctor_id = $conn->insert_id;
                    
                    // Insert availability
                    $success = true;
                    for ($i = 0; $i < count($days); $i++) {
                        if (!empty($days[$i]) && !empty($startTimes[$i]) && !empty($endTimes[$i])) {
                            $sql = "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isss", $doctor_id, $days[$i], $startTimes[$i], $endTimes[$i]);
                            
                            if (!$stmt->execute()) {
                                $success = false;
                                break;
                            }
                        }
                    }
                    
                    if ($success) {
                        // Set session variables
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['user_name'] = $fullName;
                        $_SESSION['user_type'] = $userType;
                        
                        // Redirect to doctor dashboard
                        header("Location: doctor-dashboard.php");
                        exit();
                    } else {
                        $error = "Registration failed during availability setup. Please try again.";
                        // Delete the user and doctor records if availability insertion failed
                        $conn->query("DELETE FROM doctors WHERE id = $doctor_id");
                        $conn->query("DELETE FROM users WHERE id = $user_id");
                    }
                } else {
                    $error = "Registration failed during profile creation. Please try again.";
                    // Delete the user record if doctor insertion failed
                    $conn->query("DELETE FROM users WHERE id = $user_id");
                }
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
            <a href="/Medical consultation system/doctor-register.php" class="btn-submit" style="text-decoration: none; text-align: center; display: block;">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>
