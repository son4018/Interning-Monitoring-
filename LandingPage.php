<?php

$serverName = "LAPTOP-9RL09P47\SQLEXPRESS";  // Change to your SQL Server name
$connectionOptions = [
    "Database" => "attendance",  // Change to your database name
    "Uid" => "",  
    "PWD" => ""   
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/LandingPage.css">
    <title>Internship Monitoring System</title>
</head>
<body>
<div class="overlay"></div> <!-- Dark overlay added -->
<div class="header">
    <div class="logo-container">
        <img src="image/favicon.png" alt="logo" />
        <div class="logo">Internship Monitoring System</div>
    </div>
    <button class="login-button" onclick="window.location.href='StudentLogin.php'"> Log In</button>
</div>

    <div class="container">
        <div class="title"><h3>Seamless Internship Tracking</h3></div>
        <h1>INTERNSHIP MONITORING</h1>
        <p class="subtitle"><h3>Monitor Intern Attendance, Track Hours, and Generate Reports All In One System</h3></p>
        <div class="buttons">
            <button class="btn btn-primary" onclick="window.location.href='StudentRegister.php'" >Register as Intern</button>
            <button class="btn btn-secondary" onclick="window.location.href='CompanyLogin.php'">Monitor Interns</button>
        </div>
    </div>
</body>
</html>
