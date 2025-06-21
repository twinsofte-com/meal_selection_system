<?php
require_once '../admin/db.php';
require_once '../fpdf/fpdf.php'; // Ensure this path is correct
include_once '../admin/include/date.php';

$date = date('Y-m-d'); // Use today's date or modify as needed

// Fetch report data for the selected date
$sql = "SELECT staff.name, 
               CASE 
                   WHEN sm.breakfast = 1 THEN 'Breakfast' 
                   WHEN sm.lunch = 1 THEN 'Lunch' 
                   WHEN sm.dinner = 1 THEN 'Dinner' 
                   ELSE 'None' 
               END AS meal_preference
        FROM staff_meals sm
        JOIN staff ON sm.staff_id = staff.id
        WHERE sm.meal_date = '$date'";

$result = $conn->query($sql);

// Create a new FPDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Title
$pdf->Cell(0, 10, 'Meal Preferences Report for ' . htmlspecialchars($date), 0, 1, 'C');

// Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(90, 10, 'Staff Name', 1);
$pdf->Cell(100, 10, 'Meal Preference', 1);
$pdf->Ln();

// Body
$pdf->SetFont('Arial', '', 10);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(90, 10, htmlspecialchars($row['name']), 1);
    $pdf->Cell(100, 10, htmlspecialchars($row['meal_preference']), 1);
    $pdf->Ln();
}

// Output PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="meal_report_' . $date . '.pdf"');
$pdf->Output();
exit();
?>
