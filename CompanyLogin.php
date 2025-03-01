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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if user is a company
    $sql = "SELECT email, password FROM company WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Verify hashed password
        if (password_verify($password, $row['password'])) {  
            $_SESSION['company_email'] = $email;
            $_SESSION['role'] = "company"; // Store role in session
            
            // Redirect to dashboard
            header("Location: CompanyDashboard.php");
            exit();
        } else {
            error_log("Password incorrect");
        }
    } else {
        error_log("User not found");
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
    <link rel="stylesheet" href="css/CompanyLogin.css">
    <title>Internship Monitoring System - Company Login</title>

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
            <h1 class="logintitle">Company Log In</h1>
            <form method="POST">
                <label>Email:
                    <input type="email" id="email" name="email" required>
                </label>
                <label>Password:
                    <input type="password" id="password" name="password" required>
                </label>
                <button type="submit" class="btn">Login</button>
            </form>
            <div class="account"> 
                Don't have an account? <a href="CompanyRegister.php">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>
