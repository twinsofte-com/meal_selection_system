<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';
require_once '../fpdf/fpdf.php';
include_once 'include/date.php';

$selected_date = $_GET['date'] ?? date('Y-m-d');

// Fetch only extra meal orders
$stmt = $conn->prepare("
    SELECT 
        s.staff_id,
        s.name,
        sm.breakfast,
        sm.lunch,
        sm.dinner,
        sm.manual_breakfast,
        sm.manual_lunch,
        sm.manual_dinner
    FROM staff_meals sm
    JOIN staff s ON sm.staff_id = s.id
    WHERE sm.meal_date = ?
      AND (sm.manual_breakfast = 1 OR sm.manual_lunch = 1 OR sm.manual_dinner = 1)
    ORDER BY s.name
");

$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Extra Meal Orders - $selected_date", 0, 1, 'C');
$pdf->Ln(5);

// Table Headers
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(220, 220, 220); // Light gray
$pdf->Cell(40, 10, 'Staff ID', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Breakfast', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Lunch', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Dinner', 1, 1, 'C', true);

// Table Data
$pdf->SetFont('Arial', '', 10);
while ($row = $result->fetch_assoc()) {
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(40, 10, $row['staff_id'], 1);
    $pdf->Cell(60, 10, $row['name'], 1);

    // Breakfast
    $text = $row['breakfast'] ? 'Yes' : 'No';
    if ($row['manual_breakfast']) {
        $text .= ' (Extra)';
        $pdf->SetTextColor(255, 0, 0);
    }
    $pdf->Cell(30, 10, $text, 1);
    $pdf->SetTextColor(0, 0, 0);

    // Lunch
    $text = $row['lunch'] ? 'Yes' : 'No';
    if ($row['manual_lunch']) {
        $text .= ' (Extra)';
        $pdf->SetTextColor(255, 0, 0);
    }
    $pdf->Cell(30, 10, $text, 1);
    $pdf->SetTextColor(0, 0, 0);

    // Dinner
    $text = $row['dinner'] ? 'Yes' : 'No';
    if ($row['manual_dinner']) {
        $text .= ' (Extra)';
        $pdf->SetTextColor(255, 0, 0);
    }
    $pdf->Cell(30, 10, $text, 1);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Ln();
}

// Footer with date
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, "Downloaded on: " . date('Y-m-d H:i:s'), 0, 0, 'R');

// Output
$pdf->Output('D', "Extra_Meal_Orders_Report_{$selected_date}.pdf");
exit;
