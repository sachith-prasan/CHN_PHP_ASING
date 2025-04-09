<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['task'])) {
    $id = $_POST['id'];
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    
    $sql = "UPDATE tasks SET task_name = ?, priority = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $task, $priority, $id);
    
    $response = ['success' => $stmt->execute()];
    echo json_encode($response);
}
