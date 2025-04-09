document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editTaskModal');
    const deleteModal = document.getElementById('deleteTaskModal');
    const closeButtons = document.querySelectorAll('.close');
    let taskToDelete = null;

    // Handle edit buttons
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.id;
            const taskRow = this.closest('tr');
            const taskText = taskRow.querySelector('td:first-child').textContent;
            const currentPriority = taskRow.className.replace('priority-', '');
            
            // Open modal with current task data
            document.getElementById('editTaskId').value = taskId;
            document.getElementById('editTaskName').value = taskText;
            document.getElementById('editTaskPriority').value = currentPriority;
            editModal.style.display = 'block';
        });
    });

    // Handle delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.id;
            taskToDelete = this.closest('tr');
            deleteModal.style.display = 'block';
        });
    });

    // Handle confirm delete
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (taskToDelete) {
            const taskId = taskToDelete.querySelector('.delete-btn').dataset.id;
            
            fetch('delete_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${taskId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    taskToDelete.remove();
                }
            });
            
            closeDeleteModal();
        }
    });

    // Handle cancel delete
    document.getElementById('cancelDelete').addEventListener('click', closeDeleteModal);

    // Handle edit form submission
    document.getElementById('editTaskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const taskId = document.getElementById('editTaskId').value;
        const newText = document.getElementById('editTaskName').value;
        const newPriority = document.getElementById('editTaskPriority').value;

        fetch('update_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${taskId}&task=${newText}&priority=${newPriority}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });

        closeEditModal();
    });

    // Modal close functionality
    function closeEditModal() {
        editModal.style.display = 'none';
    }

    function closeDeleteModal() {
        deleteModal.style.display = 'none';
        taskToDelete = null;
    }

    // Close buttons functionality
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal.id === 'deleteTaskModal') {
                closeDeleteModal();
            } else {
                closeEditModal();
            }
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === editModal) {
            closeEditModal();
        } else if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });
});
