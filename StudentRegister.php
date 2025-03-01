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

// Fetch registered companies
$companyQuery = "SELECT company_name FROM company";
$companyResult = $conn->query($companyQuery);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $student_id = trim($_POST['student_id']);
    $intern_name = trim($_POST['intern_name']);
    $school = trim($_POST['school']);
    $company_name = trim($_POST['company_name']); 
    $intern_department = trim($_POST['intern_department']);
    $start_date = trim($_POST['start_date']);
    $hours_required = trim($_POST['hours_required']);
    $emergency_contact = trim($_POST['emergency_contact']);
    $contact_number = trim($_POST['contact_number']);
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if student already exists
    $checkQuery = "SELECT COUNT(*) AS count FROM studentlogin WHERE student_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "<script>alert('Student ID already registered!'); window.location.href='StudentRegister.php';</script>";
        exit();
    }

    // Insert student data into the database
    $sql = "INSERT INTO studentlogin (student_id, intern_name, school, company_name, intern_department, start_date, hours_required, emergency_contact, contact_number, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error in SQL preparation: " . $conn->error);
    }

    $stmt->bind_param("ssssssisss", $student_id, $intern_name, $school, $company_name, $intern_department, $start_date, $hours_required, $emergency_contact, $contact_number, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='StudentLogin.php';</script>";
    } else {
        echo "<script>alert('Error during registration: " . addslashes($stmt->error) . "');</script>";
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
    <link rel="stylesheet" href="css/StudentRegister.css">
    <title>Internship Registration</title>

</head>
<body>
    <div class="overlay"></div>

    <div class="container">
        <h1>Register As Intern</h1>

        <form action="StudentRegister.php" method="POST" class="form-grid">
            <div class="form-group">
                <label>Student I.D</label>
                <input type="text" name="student_id" required>
            </div>

            <div class="form-group">
                <label>Intern Name</label>
                <input type="text" name="intern_name" required>
            </div>

            <div class="form-group">
                <label>School</label>
                <input type="text" name="school" required>
            </div>

            <div class="form-group">
                <label>Company Name</label>
                <select name="company_name" required>
                    <option value="">Select a company</option>
                    <?php
                    // Populate dropdown with company names from the database
                    while ($row = $companyResult->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['company_name']) . '">' . htmlspecialchars($row['company_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Intern Department</label>
                <input type="text" name="intern_department" required>
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" required>
            </div>

            <div class="form-group">
                <label>Hours Required</label>
                <input type="number" name="hours_required" required>
            </div>

            <div class="form-group">
                <label>Emergency Contact Person</label>
                <input type="text" name="emergency_contact" required>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="full-width">
                <button type="submit" class="btn">Register</button>
            </div>
        </form>

        <p class="login-link">Already have an account? <a href="StudentLogin.php">Login here</a></p>
    </div>
</body>
</html>
