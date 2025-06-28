<?php
require_once 'db.php';
require_once '../fpdf/fpdf.php';

$from = $_GET['from'] ?? date('Y-m-d');
$to = $_GET['to'] ?? date('Y-m-d');
$meal = $_GET['meal'] ?? 'all';

$sql = "SELECT 
    s.name,
    sm.meal_date,
    sm.breakfast,
    sm.lunch,
    sm.dinner,
    sm.manual_breakfast,
    sm.manual_lunch,
    sm.manual_dinner,
    sm.egg,
    sm.chicken,
    sm.vegetarian,
    sm.breakfast_received,
    sm.lunch_received,
    sm.dinner_received
FROM staff_meals sm
JOIN staff s ON s.id = sm.staff_id
WHERE sm.meal_date BETWEEN '$from' AND '$to'
ORDER BY sm.meal_date, s.name;
";

$result = $conn->query($sql);
if (!$result) {
    die("Error fetching data: " . $conn->error);
}

$total = 0;
$total_egg = 0;
$total_chicken = 0;
$total_veg = 0;
$total_breakfast = 0;
$total_lunch = 0;
$total_dinner = 0;

// extra order
$total_manual_breakfast = 0;
$total_manual_lunch = 0;
$total_manual_dinner = 0;


class PDF extends FPDF
{
    function Header()
    {
        global $from, $to, $meal;
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(40, 40, 40);
        $this->Cell(0, 10, "Meal Report - " . ucfirst($meal) . " | From $from to $to", 0, 1, 'C');
        $this->Ln(2);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Downloaded on: ' . date('Y-m-d H:i:s'), 0, 0, 'R');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetMargins(10, 20, 10);
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(220, 220, 220);
$pdf->SetTextColor(0, 0, 0);
$colWidths = [
    'date' => 25,
    'name' => 50,
    'meal' => 25,
    'received' => 30,
];

$pdf->Cell($colWidths['date'], 10, 'Date', 1, 0, 'C', true);
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

$pdf->SetFont('Arial', '', 10);
$rowToggle = false;

while ($row = $result->fetch_assoc()) {
    $rowToggle = !$rowToggle;
    $pdf->SetFillColor($rowToggle ? 245 : 255, $rowToggle ? 245 : 255, $rowToggle ? 245 : 255);
    $pdf->SetTextColor(0, 0, 0);

    $total++;
    $total_egg += $row['egg'];
    $total_chicken += $row['chicken'];
    $total_veg += $row['vegetarian'];
    if ($row['breakfast'])
        $total_breakfast++;
    if ($row['lunch'])
        $total_lunch++;
    if ($row['dinner'])
        $total_dinner++;
    // extra order
    if ($row['manual_breakfast'])
        $total_manual_breakfast++;
    if ($row['manual_lunch'])
        $total_manual_lunch++;
    if ($row['manual_dinner'])
        $total_manual_dinner++;


    $pdf->Cell($colWidths['date'], 10, $row['meal_date'], 1, 0, 'C', true);
    $pdf->Cell($colWidths['name'], 10, $row['name'], 1, 0, 'L', true);

    foreach (['breakfast', 'lunch', 'dinner'] as $type) {
        if ($meal === 'all' || $meal === $type) {
            $text = $row[$type] ? 'Yes' : 'No';
            if ($row["manual_$type"]) {
                $text .= ' (Extra)';
                $pdf->SetTextColor(225, 10, 0);
            }
            $pdf->Cell($colWidths['meal'], 10, $text, 1, 0, 'C', true);
            $pdf->SetTextColor(0, 128, 0);
            $pdf->Cell($colWidths['received'], 10, $row[$type . '_received'] ? 'Recived' : 'Pending', 1, 0, 'C', true);
            $pdf->SetTextColor(0, 0, 0);
        }
    }

    $pdf->Ln();
}

$pdf->Ln(6);
$pdf->SetFillColor(235, 235, 235);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 9, "Summary from $from to $to", 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, "Total Records: $total", 0, 1);
$pdf->Cell(0, 7, "Breakfast Ordered: $total_breakfast    Lunch Ordered: $total_lunch    Dinner Ordered: $total_dinner", 0, 1);
$pdf->Cell(0, 7, "Egg: $total_egg    Chicken: $total_chicken    Vegetarian: $total_veg", 0, 1);

// extra order
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 8, "Extra Orders:", 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, "Breakfast (Extra): $total_manual_breakfast    Lunch (Extra): $total_manual_lunch    Dinner (Extra): $total_manual_dinner", 0, 1);


header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="meal_report_' . $meal . '_' . $from . '_to_' . $to . '.pdf"');
$pdf->Output();
exit();
