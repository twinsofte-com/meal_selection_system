<?php
require_once '../../admin/db.php';
include_once '../../admin/include/date.php';

// Set dates and type safely first
$from = $_GET['from_date'] ?? date('Y-m-d');
$to = $_GET['to_date'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'all'; // staff, visitor, all

$filename = "meal_report_{$type}_{$from}_to_{$to}.csv";

// Set CSV headers
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Open output stream
$output = fopen("php://output", "w");

// Optional: Add UTF-8 BOM for Excel compatibility
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Column headers
fputcsv($output, ['Date', 'Type', 'Name', 'Breakfast', 'Breakfast Received', 'Lunch', 'Lunch Received', 'Dinner', 'Dinner Received']);

$data_written = false;

// Staff Meals
if ($type === 'staff' || $type === 'all') {
    $stmt = $conn->prepare("SELECT 
        s.name,
        sm.meal_date,
        sm.breakfast,
        sm.lunch,
        sm.dinner,
        sm.breakfast_received,
        sm.lunch_received,
        sm.dinner_received
    FROM staff_meals sm
    JOIN staff s ON sm.staff_id = s.id
    WHERE sm.meal_date BETWEEN ? AND ?
    ORDER BY sm.meal_date, s.name");
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data_written = true;
        fputcsv($output, [
            $row['meal_date'],
            'Staff',
            $row['name'],
            $row['breakfast'] ? 'Yes' : 'No',
            $row['breakfast_received'] ? 'Received' : 'Pending',
            $row['lunch'] ? 'Yes' : 'No',
            $row['lunch_received'] ? 'Received' : 'Pending',
            $row['dinner'] ? 'Yes' : 'No',
            $row['dinner_received'] ? 'Received' : 'Pending',
        ]);
    }
    $stmt->close();
}

// Visitor Meals
if ($type === 'visitor' || $type === 'all') {
    $vstmt = $conn->prepare("SELECT * FROM visitor_orders WHERE meal_date BETWEEN ? AND ?");
    $vstmt->bind_param("ss", $from, $to);
    $vstmt->execute();
    $vresult = $vstmt->get_result();

    while ($v = $vresult->fetch_assoc()) {
        $data_written = true;
        fputcsv($output, [
            $v['meal_date'],
            'Visitor',
            $v['visitor_name'],
            $v['breakfast'] ? 'Yes' : 'No',
            $v['breakfast_received'] ? 'Received' : 'Pending',
            $v['lunch'] ? 'Yes' : 'No',
            $v['lunch_received'] ? 'Received' : 'Pending',
            $v['dinner'] ? 'Yes' : 'No',
            $v['dinner_received'] ? 'Received' : 'Pending',
        ]);
    }
    $vstmt->close();
}

// If no records at all
if (!$data_written) {
    fputcsv($output, ['No records found for selected filters.']);
}

fclose($output);
exit;
