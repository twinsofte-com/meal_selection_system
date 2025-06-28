<?php
require_once 'db.php';
require_once '../fpdf/fpdf.php';
include_once 'include/date.php';

$from = $_GET['from'] ?? date('Y-m-d');
$to = $_GET['to'] ?? date('Y-m-d');
$meal = $_GET['meal'] ?? 'all';

// Fetch staff meals
$staff_sql = "SELECT 
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
ORDER BY sm.meal_date, s.name;";

$staff_result = $conn->query($staff_sql);

$visitor_sql = "SELECT * FROM visitor_orders WHERE meal_date BETWEEN '$from' AND '$to' ORDER BY meal_date, visitor_name";
$visitor_result = $conn->query($visitor_sql);

// Staff totals
$total = 0;
$total_egg = 0;
$total_chicken = 0;
$total_veg = 0;
$total_breakfast = 0;
$total_lunch = 0;
$total_dinner = 0;
$total_manual_breakfast = 0;
$total_manual_lunch = 0;
$total_manual_dinner = 0;

// Visitor totals
$total_visitors = 0;
$total_visitor_breakfast = 0;
$total_visitor_lunch = 0;
$total_visitor_dinner = 0;
$total_visitor_egg = 0;
$total_visitor_chicken = 0;
$total_visitor_veg = 0;

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

// Staff Table
$pdf->Cell(0, 10, "Staff Meals", 0, 1, 'L');
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
while ($row = $staff_result->fetch_assoc()) {
    $total++;
    $total_egg += $row['egg'];
    $total_chicken += $row['chicken'];
    $total_veg += $row['vegetarian'];
    if ($row['breakfast']) $total_breakfast++;
    if ($row['lunch']) $total_lunch++;
    if ($row['dinner']) $total_dinner++;
    if ($row['manual_breakfast']) $total_manual_breakfast++;
    if ($row['manual_lunch']) $total_manual_lunch++;
    if ($row['manual_dinner']) $total_manual_dinner++;

    $pdf->Cell($colWidths['date'], 10, $row['meal_date'], 1);
    $pdf->Cell($colWidths['name'], 10, $row['name'], 1);

    foreach (['breakfast', 'lunch', 'dinner'] as $type) {
        if ($meal === 'all' || $meal === $type) {
            $text = $row[$type] ? 'Yes' : 'No';
            if ($row["manual_$type"]) {
                $text .= ' (Extra)';
                $pdf->SetTextColor(225, 10, 0);
            }
            $pdf->Cell($colWidths['meal'], 10, $text, 1, 0, 'C');
            $pdf->SetTextColor($row[$type . '_received'] ? 0 : 220, $row[$type . '_received'] ? 128 : 20, $row[$type . '_received'] ? 0 : 60);
            $pdf->Cell($colWidths['received'], 10, $row[$type . '_received'] ? 'Received' : 'Pending', 1, 0, 'C');
            $pdf->SetTextColor(0, 0, 0); // Reset
        }
    }
    $pdf->Ln();
}

// Visitor Table
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, "Visitor Meals", 0, 1, 'L');
$pdf->Cell($colWidths['date'], 10, 'Date', 1, 0, 'C', true);
$pdf->Cell($colWidths['name'], 10, 'Visitor Name', 1, 0, 'C', true);
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
while ($v = $visitor_result->fetch_assoc()) {
    $total_visitors++;
    $total_visitor_breakfast += $v['breakfast'];
    $total_visitor_lunch += $v['lunch'];
    $total_visitor_dinner += $v['dinner'];
    $total_visitor_egg += $v['egg'];
    $total_visitor_chicken += $v['chicken'];
    $total_visitor_veg += $v['vegetarian'];

    $pdf->Cell($colWidths['date'], 10, $v['meal_date'], 1);
    $pdf->Cell($colWidths['name'], 10, $v['visitor_name'], 1);
    foreach (['breakfast', 'lunch', 'dinner'] as $type) {
        if ($meal === 'all' || $meal === $type) {
            $pdf->Cell($colWidths['meal'], 10, $v[$type] ? 'Yes' : 'No', 1, 0, 'C');
            $pdf->SetTextColor($v[$type . '_received'] ? 0 : 220, $v[$type . '_received'] ? 128 : 20, $v[$type . '_received'] ? 0 : 60);
            $pdf->Cell($colWidths['received'], 10, $v[$type . '_received'] ? 'Received' : 'Pending', 1, 0, 'C');
            $pdf->SetTextColor(0, 0, 0); // Reset
        }
    }
    $pdf->Ln();
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, "Summary from $from to $to", 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, "Staff Total: $total | Visitors: $total_visitors", 0, 1);
$pdf->Cell(0, 7, "Total Meals - Breakfast: " . ($total_breakfast + $total_visitor_breakfast) . " | Lunch: " . ($total_lunch + $total_visitor_lunch) . " | Dinner: " . ($total_dinner + $total_visitor_dinner), 0, 1);
$pdf->Cell(0, 7, "Staff Extra Orders - BreakFast: $total_manual_breakfast | Lunch: $total_manual_lunch | Dinner: $total_manual_dinner", 0, 1);
$pdf->Cell(0, 7, "Egg: ".($total_egg + $total_visitor_egg)." | Chicken: ".($total_chicken + $total_visitor_chicken)." | Veg: ".($total_veg + $total_visitor_veg), 0, 1);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="meal_report_' . $meal . '_' . $from . '_to_' . $to . '.pdf"');
$pdf->Output();
exit();
