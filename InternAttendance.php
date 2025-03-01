<?php
session_start();

$serverName = "localhost";  // Change this if needed
$username = "root";         // Default for XAMPP
$password = "";             // Default for XAMPP
$database = "internship_monitoring"; // Make sure this is the correct database name

// Connect to MySQL
$conn = new mysqli($serverName, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session
    $date = $_POST['date'];
    $status = $_POST['status'];
    $time_in = $_POST['time_in'] ?? null;
    $time_out = $_POST['time_out'] ?? null;
    $rendered_hours = 0;

    if ($status === "present" && !empty($time_in) && !empty($time_out)) {
        $time_in_dt = new DateTime($time_in);
        $time_out_dt = new DateTime($time_out);
        $interval = $time_in_dt->diff($time_out_dt);
        $rendered_hours = $interval->h + ($interval->i / 60); // Convert to decimal format
    }

    // Use MySQLi Prepared Statements
    $query = "INSERT INTO intern_attendance (student_id, date, status, time_in, time_out, rendered_hours) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("issssd", $student_id, $date, $status, $time_in, $time_out, $rendered_hours);
        if ($stmt->execute()) {
            header("Location: InternReport.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error in preparing statement: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/InternAttendance.css">
    <title>Internship Monitoring System</title>

</head>
<body>

<div class="header">
    <div class="logout-container">
        <a href="logout.php">Log Out</a>
    </div>
    <div class="logo-container">
        <img src="image/favicon.png" alt="logo" width="50" />
        <div class="logo">Internship Monitoring System</div>
    </div>
</div>

<div class="navbar">
    <a href="InternDashboard.php">Dashboard</a>
    <a href="InternAttendance.php">Attendance</a>
    <a href="InternReport.php">Reports</a>
</div>

<div class="attendance-container">
    <h2>Attendance</h2>
    <form method="POST">
        <label for="status">Select Status</label>
        <select id="status" name="status">
            <option value="">(Select an option)</option>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
        </select>

        <label for="date">Select Date</label>
        <input type="date" id="date" name="date" required>

        <label for="time_in">Time In</label>
        <input type="time" id="time_in" name="time_in" onchange="enableTimeOut()" >

        <label for="time_out">Time Out</label>
        <input type="time" id="time_out" name="time_out" onchange="enableTimeOut()" >

        <button type="submit">Submit</button>
    </form>
</div>

</body>
</html>
