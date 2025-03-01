<?php
session_start();
require('fpdf.php'); // Include the FPDF library

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

// Check if student_id is provided
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Fetch student details from StudentRegistration
    $sql_student = "SELECT student_id, intern_name, emergency_contact, intern_department FROM studentlogin WHERE student_id = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();

    if ($result_student->num_rows > 0) {
        $student = $result_student->fetch_assoc();

        // Fetch attendance records
        $sql_attendance = "SELECT date, time_in, time_out FROM intern_attendance WHERE student_id = ?";
        $stmt = $conn->prepare($sql_attendance);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result_attendance = $stmt->get_result();

        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Report Title
        $pdf->Cell(190, 10, "Intern Report - " . $student['intern_name'], 0, 1, 'C');
        $pdf->Ln(10);

        // Student Details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, "Student ID: " . $student['student_id'], 0, 1);
        $pdf->Cell(50, 10, "Name: " . $student['intern_name'], 0, 1);
        $pdf->Cell(50, 10, "Emergency Contact: " . $student['emergency_contact'], 0, 1);
        $pdf->Cell(50, 10, "Intern Department: " . $student['intern_department'], 0, 1);
        $pdf->Ln(10);

        // Attendance Table Header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 10, "Date", 1);
        $pdf->Cell(60, 10, "Time In", 1);
        $pdf->Cell(60, 10, "Time Out", 1);
        $pdf->Ln();

        // Attendance Data
        $pdf->SetFont('Arial', '', 12);
        while ($attendance = $result_attendance->fetch_assoc()) {
            $pdf->Cell(60, 10, $attendance['date'], 1);
            $pdf->Cell(60, 10, $attendance['time_in'], 1);
            $pdf->Cell(60, 10, $attendance['time_out'], 1);
            $pdf->Ln();
        }

        // Output the PDF for download
        $pdf->Output("D", "Intern_Report_" . $student_id . ".pdf");
    } else {
        echo "No student found!";
    }
} else {
    echo "Invalid request!";
}

$conn->close();
?>
