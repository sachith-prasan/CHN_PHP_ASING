<?php
require_once 'config.php';

// Sample tasks data
$tasks = [
    ['Clean the house', 'high'],
    ['Buy groceries', 'medium'],
    ['Pay bills', 'high'],
    ['Call mom', 'low'],
    ['Submit report', 'medium'],
    ['Doctor appointment', 'high'],
    ['Read book', 'low'],
    ['Team meeting', 'medium'],
    ['Exercise', 'medium'],
    ['Update resume', 'low']
];

// Prepare the insert statement
$sql = "INSERT INTO tasks (task_name, priority, created_at) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Insert each task
foreach ($tasks as $task) {
    $stmt->bind_param("ss", $task[0], $task[1]);
    if (!$stmt->execute()) {
        echo "Error inserting task '{$task[0]}': " . $stmt->error . "\n";
    } else {
        echo "Successfully added task: {$task[0]}\n";
    }
}

$stmt->close();
echo "Database seeding completed!";
?>
