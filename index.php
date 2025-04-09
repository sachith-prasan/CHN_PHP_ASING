<?php
require_once 'config.php';

// Create table if not exists (remove drop table)
$createTable = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255) NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($createTable)) {
    die("Error creating table: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task']) && isset($_POST['task'])) {
        $task = trim($_POST['task']);
        $priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'medium';
        
        // Validate priority
        if (!in_array($priority, ['low', 'medium', 'high'])) {
            $priority = 'medium';
        }
        
        $sql = "INSERT INTO tasks (task_name, priority, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        
        if (!$stmt->bind_param("ss", $task, $priority)) {
            die("Error binding parameters: " . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Add edit task handling
    if (isset($_POST['edit_task'])) {
        $taskId = $_POST['task_id'];
        $task = trim($_POST['task']);
        $priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'medium';
        
        $sql = "UPDATE tasks SET task_name = ?, priority = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssi", $task, $priority, $taskId);
            $stmt->execute();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Fetch tasks with error handling and sorting
$sql = "SELECT id, task_name, priority, created_at FROM tasks 
        ORDER BY CASE priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END, created_at DESC";

$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching tasks: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced TODO App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>📝 Task Manager</h1>
        
        <form class="task-form" method="POST">
            <div class="form-group">
                <input type="text" name="task" placeholder="Enter a new task..." required>
                <select name="priority" required>
                    <option value="low">Low Priority</option>
                    <option value="medium" selected>Medium Priority</option>
                    <option value="high">High Priority</option>
                </select>
            </div>
            <button type="submit" name="add_task">Add Task</button>
        </form>

        <div class="tasks-container">
            <h2>Task List (<?php echo $result->num_rows; ?> tasks)</h2>
            <div class="table-responsive">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="40%">Task</th>
                            <th width="15%">Priority</th>
                            <th width="20%">Created</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0):
                            $counter = 1;
                            while ($task = $result->fetch_assoc()):
                                $priorityClass = 'priority-' . htmlspecialchars($task['priority']);
                                $priorityIcon = $task['priority'] === 'high' ? '🔴' : 
                                             ($task['priority'] === 'medium' ? '🟡' : '🟢');
                        ?>
                            <tr class="<?php echo $priorityClass; ?>">
                                <td><?php echo $counter++; ?></td>
                                <td class="task-name"><?php echo htmlspecialchars($task['task_name']); ?></td>
                                <td>
                                    <span class="priority-badge">
                                        <?php echo $priorityIcon . ' ' . ucfirst($task['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($task['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <button type="button" onclick="openEditModal(<?php 
                                        echo htmlspecialchars(json_encode([
                                            'id' => $task['id'],
                                            'name' => $task['task_name'],
                                            'priority' => $task['priority']
                                        ])); 
                                    ?>)" class="edit-btn">✏️ Edit</button>
                                    <button class="delete-btn" data-id="<?php echo $task['id']; ?>">🗑️ Delete</button>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" class="no-tasks">No tasks found. Add your first task above!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Edit Task Modal -->
                <div id="editTaskModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Task</h2>
                        <form id="editTaskForm">
                            <input type="hidden" id="editTaskId">
                            <div class="form-group">
                                <label for="editTaskName">Task:</label>
                                <input type="text" id="editTaskName" required>
                            </div>
                            <div class="form-group">
                                <label for="editTaskPriority">Priority:</label>
                                <select id="editTaskPriority" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-save">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Task</h2>
                <span class="close">&times;</span>
            </div>
            <form id="editTaskForm" method="POST">
                <input type="hidden" name="task_id" id="editTaskId">
                <div class="form-group">
                    <label>Task Name</label>
                    <input type="text" name="task" id="editTaskName" required>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" id="editTaskPriority" required>
                        <option value="low">Low Priority</option>
                        <option value="medium">Medium Priority</option>
                        <option value="high">High Priority</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="edit_task" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteTaskModal" class="modal">
        <div class="modal-content delete-modal">
            <span class="close">&times;</span>
            <div class="delete-modal-body">
                <div class="delete-icon">🗑️</div>
                <h2>Delete Task</h2>
                <p>Are you sure you want to delete this task?</p>
                <div class="delete-modal-buttons">
                    <button type="button" class="cancel-btn" id="cancelDelete">Cancel</button>
                    <button type="button" class="confirm-delete-btn" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>