<?php
require_once 'db.php';
require_once  '../fpdf/fpdf.php'; // Ensure the path to FPDF is correct
include_once 'include/date.php';

$date = $_GET['date'] ?? date(format: 'Y-m-d'); // Set the date for today's report

// SQL query to fetch staff names, meal preferences, and whether the meal was received
$sql = "SELECT staff.name, 
               staff_meals.breakfast,
               staff_meals.lunch,
               staff_meals.dinner,
               staff_meals.manual_order,
               (SELECT confirmed FROM meal_issuance WHERE staff_id = staff.id AND meal_type = 'breakfast' AND meal_date = staff_meals.meal_date) AS breakfast_received,
               (SELECT confirmed FROM meal_issuance WHERE staff_id = staff.id AND meal_type = 'lunch' AND meal_date = staff_meals.meal_date) AS lunch_received,
               (SELECT confirmed FROM meal_issuance WHERE staff_id = staff.id AND meal_type = 'dinner' AND meal_date = staff_meals.meal_date) AS dinner_received
        FROM staff_meals 
        JOIN staff ON staff.id = staff_meals.staff_id 
        WHERE staff_meals.meal_date = '$date'";

$result = $conn->query($sql);

if ($result === FALSE) {
    die("Error fetching data: " . $conn->error);
}

// Create a new FPDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Title
$pdf->Cell(0, 10, 'Meal Preferences Report for ' . htmlspecialchars($date), 0, 1, 'C');
$pdf->Ln(10);

// Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 10, 'Staff Name', 1);
$pdf->Cell(20, 10, 'Breakfast', 1);
$pdf->Cell(15, 10, 'Lunch', 1);
$pdf->Cell(15, 10, 'Dinner', 1);
$pdf->Cell(25, 10, 'BF Received', 1);
$pdf->Cell(35, 10, 'Lunch Received', 1);
$pdf->Cell(35, 10, 'Dinner Received', 1);
$pdf->Ln();

// Body
$pdf->SetFont('Arial', '', 10);
while ($row = $result->fetch_assoc()) {
    // Check meal booleans
    $breakfast = $row['breakfast'] ? 'Yes' : 'No';
    $lunch = $row['lunch'] ? 'Yes' : 'No';
    $dinner = $row['dinner'] ? 'Yes' : 'No';
    $bf_received = $row['breakfast_received'] ? 'Yes' : 'No';
    $lunch_received = $row['lunch_received'] ? 'Yes' : 'No';
    $dinner_received = $row['dinner_received'] ? 'Yes' : 'No';

    // Highlight background if manually ordered
    if ($row['manual_order']) {
        $pdf->SetFillColor(255, 255, 153); // Light yellow
        $fill = true;
    } else {
        $fill = false;
    }

    $pdf->Cell(40, 10, htmlspecialchars($row['name']), 1, 0, 'L', $fill);
    $pdf->Cell(20, 10, $breakfast, 1, 0, 'C', $fill);
    $pdf->Cell(15, 10, $lunch, 1, 0, 'C', $fill);
    $pdf->Cell(15, 10, $dinner, 1, 0, 'C', $fill);
    $pdf->Cell(25, 10, $bf_received, 1, 0, 'C', $fill);
    $pdf->Cell(35, 10, $lunch_received, 1, 0, 'C', $fill);
    $pdf->Cell(35, 10, $dinner_received, 1, 0, 'C', $fill);

    $pdf->Ln();
}


// Output PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="meal_report_' . $date . '.pdf"');
$pdf->Output();
exit();
?>
