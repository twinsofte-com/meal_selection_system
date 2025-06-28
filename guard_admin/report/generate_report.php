<?php
require_once '../../admin/db.php';
require_once '../../fpdf/fpdf.php';

$from = $_GET['from'] ?? date('Y-m-d');
$to = $_GET['to'] ?? date('Y-m-d');
$meal = $_GET['meal'] ?? 'all';

class PDF extends FPDF
{
    function Header()
    {
        global $from, $to, $meal;
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, "Meal Report - " . ucfirst($meal) . " | From $from to $to", 0, 1, 'C');
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Generated: ' . date('Y-m-d H:i:s'), 0, 0, 'R');
    }
}

// Fetch Staff
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
ORDER BY sm.meal_date, s.name";

$staff_result = $conn->query($staff_sql);

// Fetch Visitors
$visitor_sql = "SELECT * FROM visitor_orders WHERE meal_date BETWEEN '$from' AND '$to' ORDER BY meal_date, visitor_name";
$visitor_result = $conn->query($visitor_sql);

// Totals
$total = 0;
$total_visitors = 0;
$total_egg = 0;
$total_chicken = 0;
$total_veg = 0;
$total_breakfast = 0;
$total_lunch = 0;
$total_dinner = 0;
$total_manual_breakfast = 0;
$total_manual_lunch = 0;
$total_manual_dinner = 0;

$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetMargins(10, 20, 10);
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(220, 220, 220);
$w = ['date' => 25, 'name' => 50, 'meal' => 25, 'recv' => 30];

// Table Head
$pdf->Cell($w['date'], 10, 'Date', 1, 0, 'C', true);
$pdf->Cell($w['name'], 10, 'Name', 1, 0, 'C', true);
foreach (['breakfast' => 'BF', 'lunch' => 'Lunch', 'dinner' => 'Dinner'] as $m => $label) {
    if ($meal === 'all' || $meal === $m) {
        $pdf->Cell($w['meal'], 10, $label, 1, 0, 'C', true);
        $pdf->Cell($w['recv'], 10, "$label Received", 1, 0, 'C', true);
    }
}
$pdf->Ln();

// === Staff Rows ===
$pdf->SetFont('Arial', '', 10);
while ($row = $staff_result->fetch_assoc()) {
    $pdf->Cell($w['date'], 10, $row['meal_date'], 1);
    $pdf->Cell($w['name'], 10, $row['name'], 1);
    foreach (['breakfast', 'lunch', 'dinner'] as $m) {
        if ($meal === 'all' || $meal === $m) {
            $txt = $row[$m] ? 'Yes' : 'No';
            if ($row["manual_$m"])
                $txt .= ' (Extra)';
            $recv = $row["{$m}_received"];

            // Meal Ordered
            $pdf->Cell($w['meal'], 10, $txt, 1);

            // Received status with color
            if (!$row[$m]) {
                $pdf->Cell($w['recv'], 10, '-', 1, 0, 'C'); // No order placed
            } elseif ($recv) {
                $pdf->SetTextColor(0, 128, 0); // Green
                $pdf->Cell($w['recv'], 10, 'Received', 1, 0, 'C');
            } else {
                $pdf->SetTextColor(220, 20, 60); // Red
                $pdf->Cell($w['recv'], 10, 'Pending', 1, 0, 'C');
            }

            $pdf->SetTextColor(0, 0, 0); // Reset
        }
    }
    $pdf->Ln();
    $total++;
    $total_breakfast += $row['breakfast'];
    $total_lunch += $row['lunch'];
    $total_dinner += $row['dinner'];
    $total_egg += $row['egg'];
    $total_chicken += $row['chicken'];
    $total_veg += $row['vegetarian'];
    $total_manual_breakfast += $row['manual_breakfast'];
    $total_manual_lunch += $row['manual_lunch'];
    $total_manual_dinner += $row['manual_dinner'];
}


// === Visitor Rows ===
if ($visitor_result->num_rows > 0) {
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 10, 'Visitor Orders', 0, 1);
    $pdf->SetFont('Arial', '', 10);

    while ($v = $visitor_result->fetch_assoc()) {
        $pdf->Cell($w['date'], 10, $v['meal_date'], 1);
        $pdf->Cell($w['name'], 10, $v['visitor_name'] . ' (V)', 1);
        foreach (['breakfast', 'lunch', 'dinner'] as $m) {
            if ($meal === 'all' || $meal === $m) {
                $txt = $v[$m] ? 'Yes' : 'No';
                $recv = $v["{$m}_received"];

                $pdf->Cell($w['meal'], 10, $txt, 1);
                if (!$v[$m]) {
                    $pdf->Cell($w['recv'], 10, '-', 1, 0, 'C'); // No order
                } elseif ($recv) {
                    $pdf->SetTextColor(0, 128, 0); // Green
                    $pdf->Cell($w['recv'], 10, 'Received', 1, 0, 'C');
                } else {
                    $pdf->SetTextColor(220, 20, 60); // Red
                    $pdf->Cell($w['recv'], 10, 'Pending', 1, 0, 'C');
                }

                $pdf->SetTextColor(0, 0, 0); // Reset
            }
        }
        $pdf->Ln();
        $total_visitors++;
        $total_breakfast += $v['breakfast'];
        $total_lunch += $v['lunch'];
        $total_dinner += $v['dinner'];
        $total_egg += $v['egg'];
        $total_chicken += $v['chicken'];
        $total_veg += $v['vegetarian'];
    }
}


// === Summary Section ===
$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Summary', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, "Total Staff: $total", 0, 1);
$pdf->Cell(0, 8, "Total Visitors: $total_visitors", 0, 1);
$pdf->Cell(0, 8, "Breakfast: $total_breakfast   Lunch: $total_lunch   Dinner: $total_dinner", 0, 1);
$pdf->Cell(0, 8, "Egg: $total_egg   Chicken: $total_chicken   Veg: $total_veg", 0, 1);
$pdf->Cell(0, 8, "Extra BF: $total_manual_breakfast   Extra Lunch: $total_manual_lunch   Extra Dinner: $total_manual_dinner", 0, 1);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="meal_report_' . $meal . '_' . $from . '_to_' . $to . '.pdf"');
$pdf->Output();
exit;
