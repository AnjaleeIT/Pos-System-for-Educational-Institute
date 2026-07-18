<?php
require 'config.php';
header('Content-Type: application/json');

$student_id = $_GET['student_id'] ?? '';

if ($student_id != '') {
    
    $stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'name' => $row['name']]);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>