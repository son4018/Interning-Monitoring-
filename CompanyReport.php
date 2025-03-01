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

// Fetch interns assigned to this company
$sql_interns = "SELECT student_id, intern_name FROM studentlogin WHERE company_name = ?";
$stmt = $conn->prepare($sql_interns);
$stmt->bind_param("s", $company_name);
$stmt->execute();
$result_interns = $stmt->get_result();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet"href="css/CompanyReport.css">
    <title>Internship Monitoring System</title>

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
    <h2>Summary Report</h2>
    <table class="report-table">
        <tr>
            <th>Student ID</th>
            <th>Intern Name</th>
            <th>Download</th>
        </tr>
        <?php while ($row = $result_interns->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['intern_name']); ?></td>
                <td><a href="DownloadReport.php?student_id=<?php echo $row['student_id']; ?>">Download</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>


    </body>
</html>