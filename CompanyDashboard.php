<?php
session_start();
if (!isset($_SESSION['company_email'])) {
    header("Location: CompanyLogin.php"); // Redirect to CompanyLogin.php
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

// Fetch company details
$sql_company = "SELECT * FROM company WHERE email = ?";
$stmt = $conn->prepare($sql_company);
$stmt->bind_param("s", $company_email);
$stmt->execute();
$result_company = $stmt->get_result();

if ($result_company->num_rows > 0) {
    $company = $result_company->fetch_assoc();
    $company_name = $company['company_name']; // Get company name
} else {
    session_destroy();
    header("Location: CompanyLogin.php?error=CompanyNotFound");
    exit();
}

// Count number of interns assigned to this company
$sql_count = "SELECT COUNT(*) as total_interns FROM studentlogin WHERE company_name = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("s", $company_name);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row_count = $result_count->fetch_assoc();
$total_interns = $row_count['total_interns']; // Store the total interns count

// Get today's date
$today = date('Y-m-d');

// Count interns who checked in today for the logged-in company
$sql_present_interns = "
    SELECT COUNT(DISTINCT student_id) AS present_interns 
    FROM intern_attendance 
    WHERE date = ? 
    AND student_id IN (
        SELECT student_id FROM studentlogin WHERE company_name = ?
    )
";

$stmt_present = $conn->prepare($sql_present_interns);
$stmt_present->bind_param("ss", $today, $company_name); 
$stmt_present->execute();
$result_present = $stmt_present->get_result();
$row_present = $result_present->fetch_assoc();
$present_interns = $row_present['present_interns'] ?? 0;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/CompanyDashboard.css">
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

    <!-- Rendered Hours & Remaining Hours Section -->
    <div class="container">
        <div class="box1">
            No. of Interns
            <div class="value"><?php echo $total_interns; ?></div>
        </div>
        <div class="box2">
            Interns Present
            <div class="value"><?php echo $present_interns; ?></div>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
