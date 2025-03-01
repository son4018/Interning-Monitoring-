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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = trim($_POST["company_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $repeat_password = $_POST["repeat_password"];

    // Validate if passwords match
    if ($password !== $repeat_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if email already exists
        $checkQuery = "SELECT * FROM company WHERE email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email already exists! Please use a different email.');</script>";
        } else {
            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new company record
            $insertQuery = "INSERT INTO company (company_name, email, password) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("sss", $company_name, $email, $hashedPassword);

            if ($insertStmt->execute()) {
                echo "<script>alert('Registration successful! Please log in.'); window.location.href='CompanyLogin.php';</script>";
            } else {
                echo "<script>alert('Error registering. Please try again.');</script>";
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/CompanyRegister.css">
    <title>Internship Registration</title>

</head>
<body>
    <div class="overlay"></div>

    <div class="container">
        <h1>Register As Company</h1>

        <form action="CompanyRegister.php" method="POST" class="form-grid">
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" required>
            </div>

            <div class="form-group">
                <label>Company Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Repeat Password</label>
                <input type="password" name="repeat_password" required>
            </div>

            <div class="full-width">
                <button type="submit" class="btn">Register</button>
            </div>
        </form>

        <p class="login-link">Already have an account? <a href="StudentLogin.php">Login here</a></p>
    </div>
</body>
</html>
