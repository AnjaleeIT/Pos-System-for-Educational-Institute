<?php
// fetch_student.php
require 'config.php';

if (isset($_GET['student_id'])) {
    $student_id = trim($_GET['student_id']);
    
    $stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ? LIMIT 1");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
       
        echo json_encode(['success' => true, 'found' => true, 'name' => $row['name']]);
    } else {
        
        echo json_encode(['success' => true, 'found' => false, 'message' => 'New Student! Enter name manually.']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid Request']);
?>