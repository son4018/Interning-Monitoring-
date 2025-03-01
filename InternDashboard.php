<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: StudentLogin.php");
    exit();
}

// Database connection
$host = "localhost"; 
$user = "root"; 
$password = ""; 
$database = "internship_monitoring"; 

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from session
$student_id = $_SESSION['student_id'];

// Fetch student details
$sql_student = "SELECT * FROM studentlogin WHERE student_id = '$student_id'";
$result_student = $conn->query($sql_student);

if ($result_student->num_rows > 0) {
    $student = $result_student->fetch_assoc();
    $hours_required = $student['hours_required'];
} else {
    die("Student not found.");
}

// Fetch total rendered hours from attendance records
$sql_rendered_hours = "SELECT SUM(rendered_hours) AS total_rendered FROM intern_attendance WHERE student_id = '$student_id'";
$result_rendered_hours = $conn->query($sql_rendered_hours);
$row = $result_rendered_hours->fetch_assoc();
$renderedHours = $row['total_rendered'] ?? 0;

// Calculate remaining hours
$remainingHours = $hours_required - $renderedHours;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/InternDashboard.css">

    <title>Internship Monitoring System</title>

</head>
<body>

    <!-- Header -->
    <div class="header">
        <!-- Logout Button -->
        <div class="logout-container">
            <a href="logout.php">Log Out</a>
        </div>

        <div class="logo-container">
            <img src="image/favicon.png" alt="logo" width="50" />
            <div class="logo">Internship Monitoring System</div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="InternDashboard.php">Dashboard</a>
        <a href="InternAttendance.php">Attendance</a>
        <a href="InternReport.php">Reports</a>
    </div>

    <!-- Rendered Hours & Remaining Hours Section -->
    <div class="container">
        <div class="box1">
            Rendered Hours
            <div class="value"><?php echo $renderedHours; ?></div>
        </div>
        <div class="box2">
            Remaining Hours
            <div class="value"><?php echo $remainingHours; ?></div>
        </div>
    </div>

    <div class="gauge-container">
    <canvas id="gaugeChart"></canvas>
    <div class="gauge-text">
        <?php echo round(($renderedHours / $hours_required) * 100, 2); ?>% Completed
    </div>
    </div>

    <script>
    const ctx = document.getElementById('gaugeChart').getContext('2d');
    const renderedHours = <?php echo $renderedHours; ?>;
    const requiredHours = <?php echo $hours_required; ?>;
    const percentage = (renderedHours / requiredHours) * 100;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [renderedHours, requiredHours - renderedHours], 
                backgroundColor: [
                    'rgba(14, 121, 17, 1)',   // Green for completed hours
                    'rgb(200,200,200, 1)'  // Light gray for remaining hours
                ],
                hoverBackgroundColor: [
                    'rgba(23, 185, 28, 1)',   // Slightly brighter green on hover
                    'rgb(150, 148, 148)'  
                ],
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            rotation: -90,         // Start from top
            circumference: 180,     // Half-circle
            cutout: '70%',         // Inner cutout for gauge effect
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                animateRotate: true, // Smooth animation
                duration: 1500
            },
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
</script>


</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
