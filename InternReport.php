<?php
session_start(); // Ensure the session is started before accessing session variables

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    die("Error: Student is not logged in.");
}
$host = "localhost"; // Change if needed
$user = "root"; // Change if needed
$password = ""; // Change if needed
$database = "internship_monitoring"; // Change this to your actual database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get Student ID from session or GET parameter
$student_id = $_SESSION['student_id']; // Change this or get from session `$_SESSION['student_id']`

// Fetch student details
$sql_student = "SELECT * FROM studentlogin WHERE student_id = '$student_id'";
$result_student = $conn->query($sql_student);

if ($result_student->num_rows > 0) {
    $student = $result_student->fetch_assoc();
} else {
    die("Student not found.");
}

// Fetch attendance records

$sql_attendance = "SELECT * FROM intern_attendance WHERE student_id = '$student_id' ORDER BY date DESC";
$result_attendance = $conn->query($sql_attendance);


$attendance_records = [];
while ($row = $result_attendance->fetch_assoc()) {
    $attendance_records[] = $row;
}

// Calculate Total Rendered Hours & Remaining Hours
$total_rendered = array_sum(array_column($attendance_records, "rendered_hours"));
$remaining_hours = $student['hours_required'] - $total_rendered;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/InternReport.css">
    <title>Student Internship Report</title>

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

    <div class="report-container">
        <h2>Student Internship Report</h2>
        <p><strong>Student ID:</strong> <?php echo $student['student_id']; ?></p>
        <p><strong>Student Name:</strong> <?php echo $student['intern_name']; ?></p>

        <table class="report-table">
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Rendered Hours</th>
                <th>Hours Required</th>
                <th>Remaining Hours</th>
            </tr>
            <?php
            $current_total = 0;
            foreach ($attendance_records as $record) {
                $current_total += $record["rendered_hours"];
                $remaining = $student['hours_required'] - $current_total;
                echo "<tr>
                        <td>{$record['date']}</td>
                        <td><span class='status " . ($record['status'] == 'Present' ? 'status-present' : 'status-absent') . "'>{$record['status']}</span></td>
                        <td>{$record['rendered_hours']}</td>
                        <td>{$student['hours_required']}</td>
                        <td>{$remaining}</td>
                      </tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
