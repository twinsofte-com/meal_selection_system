<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';
require_once '../fpdf/fpdf.php';  // Make sure the FPDF library is included

// Check for selected date or use today's date
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch meal data for selected date
$stmt = $conn->prepare("
    SELECT 
        s.staff_id,
        s.name,
        sm.breakfast,
        sm.lunch,
        sm.dinner,
        sm.vegetarian,
        sm.egg,
        sm.chicken,
        sm.meal_date,
        sm.manual_order
    FROM staff_meals sm
    JOIN staff s ON sm.staff_id = s.id
    WHERE sm.meal_date = ?
    ORDER BY s.name
");

$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();

// Create a new FPDF instance
$pdf = new FPDF();
$pdf->AddPage();

// Set title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Meal Orders Report for " . htmlspecialchars($selected_date), 0, 1, 'C');

// Add table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(25, 10, 'Staff ID', 1);
$pdf->Cell(50, 10, 'Name', 1);
$pdf->Cell(25, 10, 'Breakfast', 1);
$pdf->Cell(25, 10, 'Lunch', 1);
$pdf->Cell(25, 10, 'Dinner', 1);
$pdf->Cell(25, 10, 'Vegetarian', 1);
$pdf->Cell(25, 10, 'Egg', 1);
$pdf->Cell(25, 10, 'Chicken', 1);
$pdf->Ln();

// Populate the table with meal data
$pdf->SetFont('Arial', '', 12);
while ($row = $result->fetch_assoc()) {
    // Check if the meal is a manual order
    if ($row['manual_order'] == 1) {
        $pdf->SetFillColor(255, 223, 186);  // Light Yellow background for manual orders
    } else {
        $pdf->SetFillColor(255, 255, 255);  // White background for regular orders
    }
    
    // Staff ID
    $pdf->Cell(25, 10, htmlspecialchars($row['staff_id']), 1, 0, 'C', true);
    // Name
    $pdf->Cell(50, 10, htmlspecialchars($row['name']), 1, 0, 'C', true);

    // Breakfast
    if ($row['breakfast']) {
        $pdf->SetFillColor(0, 255, 0);  // Green for Yes
        $pdf->Cell(25, 10, 'Yes', 1, 0, 'C', true);
    } else {
        $pdf->SetFillColor(255, 0, 0);  // Red for No
        $pdf->Cell(25, 10, 'No', 1, 0, 'C', true);
    }

    // Lunch
    if ($row['lunch']) {
        $pdf->SetFillColor(0, 255, 0);  // Green for Yes
        $pdf->Cell(25, 10, 'Yes', 1, 0, 'C', true);
    } else {
        $pdf->SetFillColor(255, 0, 0);  // Red for No
        $pdf->Cell(25, 10, 'No', 1, 0, 'C', true);
    }

    // Dinner
    if ($row['dinner']) {
        $pdf->SetFillColor(0, 255, 0);  // Green for Yes
        $pdf->Cell(25, 10, 'Yes', 1, 0, 'C', true);
    } else {
        $pdf->SetFillColor(255, 0, 0);  // Red for No
        $pdf->Cell(25, 10, 'No', 1, 0, 'C', true);
    }

    // Vegetarian preference
    if ($row['vegetarian']) {
        $pdf->SetFillColor(0, 255, 0);  // Green for Yes
        $pdf->Cell(25, 10, 'Yes', 1, 0, 'C', true);
    } else {
        $pdf->SetFillColor(255, 0, 0);  // Red for No
        $pdf->Cell(25, 10, 'No', 1, 0, 'C', true);
    }

    // Egg preference
    if ($row['egg']) {
        $pdf->SetFillColor(0, 255, 0);  // Green for Yes
        $pdf->Cell(25, 10, 'Yes', 1, 0, 'C', true);
    } else {
        $pdf->SetFillColor(255, 0, 0);  // Red for No
        $pdf->Cell(25, 10, 'No', 1, 0, 'C', true);
    }

    // Chicken preference
    if ($row['chicken']) {
        $pdf->SetFillColor(0, 255, 0);  // Green for Yes
        $pdf->Cell(25, 10, 'Yes', 1, 0, 'C', true);
    } else {
        $pdf->SetFillColor(255, 0, 0);  // Red for No
        $pdf->Cell(25, 10, 'No', 1, 0, 'C', true);
    }

    $pdf->Ln();
}

// Output the PDF to the browser for download
$pdf->Output('D', "Meal_Orders_Report_{$selected_date}.pdf");
exit;
?>
