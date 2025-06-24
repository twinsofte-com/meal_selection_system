<?php
require_once 'db.php';
require_once '../fpdf/fpdf.php';

$date = $_GET['date'] ?? date('Y-m-d');
$meal = $_GET['meal'] ?? 'all';

$sql = "SELECT 
            s.name,
            sm.breakfast,
            sm.lunch,
            sm.dinner,
            sm.manual_breakfast,
            sm.manual_lunch,
            sm.manual_dinner,
            (SELECT confirmed FROM meal_issuance WHERE staff_id = s.id AND meal_type = 'breakfast' AND meal_date = sm.meal_date) AS breakfast_received,
            (SELECT confirmed FROM meal_issuance WHERE staff_id = s.id AND meal_type = 'lunch' AND meal_date = sm.meal_date) AS lunch_received,
            (SELECT confirmed FROM meal_issuance WHERE staff_id = s.id AND meal_type = 'dinner' AND meal_date = sm.meal_date) AS dinner_received
        FROM staff_meals sm
        JOIN staff s ON s.id = sm.staff_id
        WHERE sm.meal_date = '$date'
        ORDER BY s.name";

$result = $conn->query($sql);
if (!$result) {
    die("Error fetching data: " . $conn->error);
}

// Setup PDF
$pdf = new FPDF('L', 'mm', 'A4'); // Landscape
$pdf->SetMargins(10, 15, 10);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Meal Report - " . ucfirst($meal) . " - $date", 0, 1, 'C');
$pdf->Ln(3);

// Header Styling
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(220, 220, 220); // Light gray
$colWidths = [
    'name' => 50,
    'meal' => 25,
    'received' => 30,
];

// Header Row
$pdf->Cell($colWidths['name'], 10, 'Staff Name', 1, 0, 'C', true);
if ($meal === 'all' || $meal === 'breakfast') {
    $pdf->Cell($colWidths['meal'], 10, 'Breakfast', 1, 0, 'C', true);
    $pdf->Cell($colWidths['received'], 10, 'BF Received', 1, 0, 'C', true);
}
if ($meal === 'all' || $meal === 'lunch') {
    $pdf->Cell($colWidths['meal'], 10, 'Lunch', 1, 0, 'C', true);
    $pdf->Cell($colWidths['received'], 10, 'Lunch Received', 1, 0, 'C', true);
}
if ($meal === 'all' || $meal === 'dinner') {
    $pdf->Cell($colWidths['meal'], 10, 'Dinner', 1, 0, 'C', true);
    $pdf->Cell($colWidths['received'], 10, 'Dinner Received', 1, 0, 'C', true);
}
$pdf->Ln();

// Table Data
$pdf->SetFont('Arial', '', 10);
while ($row = $result->fetch_assoc()) {
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($colWidths['name'], 10, $row['name'], 1);

    // Breakfast
    if ($meal === 'all' || $meal === 'breakfast') {
        $text = $row['breakfast'] ? 'Yes' : 'No';
        if ($row['manual_breakfast']) {
            $text .= ' (Extra)';
            $pdf->SetTextColor(255, 0, 0);
        }
        $pdf->Cell($colWidths['meal'], 10, $text, 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colWidths['received'], 10, $row['breakfast_received'] ? 'Yes' : 'No', 1);
    }

    // Lunch
    if ($meal === 'all' || $meal === 'lunch') {
        $text = $row['lunch'] ? 'Yes' : 'No';
        if ($row['manual_lunch']) {
            $text .= ' (Extra)';
            $pdf->SetTextColor(255, 0, 0);
        }
        $pdf->Cell($colWidths['meal'], 10, $text, 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colWidths['received'], 10, $row['lunch_received'] ? 'Yes' : 'No', 1);
    }

    // Dinner
    if ($meal === 'all' || $meal === 'dinner') {
        $text = $row['dinner'] ? 'Yes' : 'No';
        if ($row['manual_dinner']) {
            $text .= ' (Extra)';
            $pdf->SetTextColor(255, 0, 0);
        }
        $pdf->Cell($colWidths['meal'], 10, $text, 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colWidths['received'], 10, $row['dinner_received'] ? 'Yes' : 'No', 1);
    }

    $pdf->Ln();
}

// Footer
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, 'Downloaded on: ' . date('Y-m-d H:i:s'), 0, 0, 'R');

// Output
header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="meal_report_' . $meal . '_' . $date . '.pdf"');
$pdf->Output();
exit();
