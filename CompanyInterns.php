<?php
session_start();
if (!isset($_SESSION['company_email'])) {
    header("Location: CompanyLogin.php");
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

// Get company email from session
$company_email = $_SESSION['company_email'];

// Fetch company name
$sql_company = "SELECT company_name FROM company WHERE email = ?";
$stmt = $conn->prepare($sql_company);
$stmt->bind_param("s", $company_email);
$stmt->execute();
$result_company = $stmt->get_result();

if ($result_company->num_rows > 0) {
    $company = $result_company->fetch_assoc();
    $company_name = $company['company_name'];
} else {
    session_destroy();
    header("Location: CompanyLogin.php?error=CompanyNotFound");
    exit();
}

// Fetch interns assigned to this company with rendered hours
// Get today's date
$today = date('Y-m-d');

// Fetch interns assigned to this company with rendered hours and attendance status
$sql_interns = "
    SELECT s.student_id, s.intern_name, s.hours_required, 
           COALESCE(SUM(a.rendered_hours), 0) AS rendered_hours,
           CASE 
               WHEN EXISTS (SELECT 1 FROM intern_attendance ia WHERE ia.student_id = s.student_id AND ia.date = ?) 
               THEN 'Present' 
               ELSE 'Absent' 
           END AS attendance_status
    FROM studentlogin s
    LEFT JOIN intern_attendance a ON s.student_id = a.student_id
    WHERE s.company_name = ?
    GROUP BY s.student_id, s.intern_name, s.hours_required
";

$stmt = $conn->prepare($sql_interns);
$stmt->bind_param("ss", $today, $company_name);
$stmt->execute();
$result_interns = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Monitoring System</title>
    <style> 
        body {
            font-family: Arial, sans-serif;
            background-color: #DBDBDB;
            margin: 0;
            padding: 0;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background-color: #E5EFE4;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            font-weight: bold;
            font-size: 25px;
        }

        .logout-container {
            position: absolute;
            right: 20px;
        }

        .logout-container a {
            text-decoration: none;
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }

        .logout-container a:hover {
            background-color: #c0392b;
        }

        .navbar {
            display: flex;
            justify-content: center;
            background-color: #0E7911;
            padding: 10px 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            font-size: 18px;
        }

        .navbar a:hover {
            background-color: rgb(23, 185, 28);
            border-radius: 5px;
        }

        .report-container { 
            width: 80%; 
            margin: 40px auto; 
            background: white; 
            border-radius: 15px; 
            padding: 20px; 
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); 
        }
        
        .report-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        
        .report-table th, .report-table td { 
            padding: 12px; 
            text-align: center; 
            border: 1px solid #ddd; 
        }

        .report-table th { 
            background-color: #0E7911; 
            color: white; 
        }
        .company-name {
    position: absolute;
    right: 120px;
    font-size: 18px;
    font-weight: bold;
}

    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
    <!-- Display Logged-in Company Name -->
    <div class="company-name">
        Logged in as: <?php echo htmlspecialchars($company_name); ?>
    </div>

    <!-- Logout Button -->
    <div class="logout-container">
        <a href="logout.php">Log Out</a>
    </div>

    <!-- Logo -->
    <div class="logo-container">
        <img src="image/favicon.png" alt="logo" width="50" />
        <div class="logo">Internship Monitoring System</div>
    </div>
</div>


    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="CompanyDashboard.php">Dashboard</a>
        <a href="CompanyInterns.php">Interns</a>
        <a href="CompanyReport.php">Reports</a>
    </div>

    <div class="report-container">
        <h2>Student Internship List</h2>
        <table class="report-table">
        <tr>
    <th>Student ID</th>
    <th>Intern Name</th>
    <th>Attendance</th> <!-- New Column -->
    <th>Rendered Hours</th>
    <th>Hours Required</th>
    <th>Remaining Hours</th>
</tr>

<?php while ($intern = $result_interns->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($intern['student_id']); ?></td>
        <td><?php echo htmlspecialchars($intern['intern_name']); ?></td>
        <td><?php echo htmlspecialchars($intern['attendance_status']); ?></td> <!-- Display "Present" or "Absent" -->
        <td><?php echo htmlspecialchars($intern['rendered_hours']); ?></td>
        <td><?php echo htmlspecialchars($intern['hours_required']); ?></td>
        <td><?php echo htmlspecialchars($intern['hours_required'] - $intern['rendered_hours']); ?></td>
    </tr>
<?php endwhile; ?>

        </table>
    </div>

</body>
</html>

<?php $conn->close(); ?>
