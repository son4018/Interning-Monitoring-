<?php
session_start();

// Database connection
$serverName = "localhost";  
$dbUsername = "root";  
$dbPassword = "";  
$dbName = "internship_monitoring";  

$conn = new mysqli($serverName, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    // Check if user exists
    $sql = "SELECT student_id, password FROM studentlogin WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Verify the hashed password
        if (password_verify($password, $row['password'])) {
            $_SESSION['student_id'] = $user_id;
            $_SESSION['role'] = "student"; // Store role in session

            // Redirect to dashboard
            header("Location: InternDashboard.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.');</script>";
        }
    } else {
        echo "<script>alert('User not found.');</script>";
    }

    $stmt->close();
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/StudentLogin.css">
    <title>Internship Monitoring System</title>

</head>
<body>
    <div class="overlay"></div>

    <div class="header">
        <div class="logo-container">
            <img src="image/favicon.png" alt="logo" />
            <div class="logo">Internship Monitoring System</div>
        </div>
    </div>

    <div class="container">
        <div class="text">
            <h1>Monitor</h1>
            <p>Keep a close watch on intern progress report in real time</p>
            <h1>Track Hours</h1>
            <p>Accurate log rendered hours with ease</p>
            <h1>Generate Reports</h1>
            <p>Create detailed internship reports</p>
        </div>

        <div class="registration">
            <h1 class="logintitle">Log In</h1>
            <form method="POST">
                <label>Username:
                    <input type="text" id="student_id" name="student_id" required>
                </label>
                <label>Password:
                    <input type="password" id="password" name="password" required>
                </label>
                <button type="submit" class="btn">Login</button>
            </form>
            <div class="account"> 
                Don't have an account? <a href="StudentRegister.php">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>



