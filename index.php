<?php
// Start session to store data across requests
session_start();

// Check if CRUD list exists in the session, if not, initialize it
if (!isset($_SESSION['crud_list'])) {
    $_SESSION['crud_list'] = [];
}

// Process POST request to add or update records
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['task']) && isset($_POST['status'])) {
        $task = htmlspecialchars($_POST['task']); // Sanitize the input to prevent XSS attacks
        $status = $_POST['status']; // Get the status
        $timestamp = date('Y-m-d H:i:s'); // Get the current time
        if (!empty($task)) {
            // Check if there's an ID for updating the record
            if (isset($_POST['task_id']) && $_POST['task_id'] !== '') {
                $id = intval($_POST['task_id']);
                $_SESSION['crud_list'][$id] = ['task' => $task, 'status' => $status, 'timestamp' => $timestamp]; // Update the existing record
            } else {
                // Add a new record if no ID exists
                $_SESSION['crud_list'][] = ['task' => $task, 'status' => $status, 'timestamp' => $timestamp];
            }
        }
    }

    // Process deletion of a specific record
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        unset($_SESSION['crud_list'][$id]); // Remove the record from the list
        $_SESSION['crud_list'] = array_values($_SESSION['crud_list']); // Reindex the array to keep it sequential
    }
}

// Variable to store search results
$search_results = $_SESSION['crud_list'];

// Handle search functionality
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $search_query = htmlspecialchars($_GET['search']); // Sanitize the search input
    $filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : ''; // Get the filter status
    if (!empty($search_query) || !empty($filter_status)) {
        // Filter the tasks array based on search query and filter status
        $search_results = array_filter($_SESSION['crud_list'], function ($taskData) use ($search_query, $filter_status) {
            $matchQuery = stripos($taskData['task'], $search_query) !== false;
            $matchStatus = $filter_status ? $taskData['status'] === $filter_status : true;
            return $matchQuery && $matchStatus;
        });
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CRUD Application with Animation</title>

    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Add Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center text-primary mb-4 animate__animated animate__bounce">CRUD Application with Animation</h1>

        <!-- Form to add or edit a record -->
        <form action="" method="POST" class="shadow p-4 rounded bg-white">
            <input type="hidden" name="task_id" id="task_id">
            <div class="mb-3">
                <label for="task" class="form-label">Task:</label>
                <input type="text" id="task" name="task" class="form-control"
                    placeholder="Enter your task here" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 animate__animated">Save</button>
        </form>

        <!-- Search form -->
        <form action="" method="GET" class="mt-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search tasks..."
                    value="<?php echo isset($search_query) ? $search_query : ''; ?>">
                <select name="filter_status" class="form-control">
                    <option value="">Filter by status</option>
                    <option value="completed" <?php echo isset($filter_status) && $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo isset($filter_status) && $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
                <button type="submit" class="btn btn-secondary">Search</button>
            </div>
        </form>

        <!-- Display the list of records -->
        <div class="mt-4">
            <h3 class="text-secondary">Your Records:</h3>
            <?php if (!empty($search_results)): ?>
            <ul class="list-group">
                <?php foreach ($search_results as $id => $taskData): ?>
                <li
                    class="list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn">
                    <span><?php echo $taskData['task']; ?> (<?php echo ucfirst($taskData['status']); ?>)</span>
                    <small class="text-muted"><?php echo $taskData['timestamp']; ?></small>
                    <div>
                        <button class="btn btn-sm btn-success"
                            onclick="editTask(<?php echo $id; ?>, '<?php echo $taskData['task']; ?>', '<?php echo $taskData['status']; ?>')">Edit</button>
                        <form action="" method="POST" class="d-inline">
                            <input type="hidden" name="delete_id" value="<?php echo $id; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-muted">No records found. Add a new record above!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        // Function to populate the form for editing a task
        function editTask(id, task, status) {
            document.getElementById('task_id').value = id; // Set the task ID
            document.getElementById('task').value = task; // Set the task value in the input field
            document.getElementById('status').value = status; // Set the status in the dropdown
        }

        // Add a pulse animation when the save button is clicked
        const saveButton = document.querySelector('button[type="submit"]');
        saveButton.addEventListener('click', function() {
            saveButton.classList.add('animate__pulse'); // Add the pulse animation
            setTimeout(() => saveButton.classList.remove('animate__pulse'),
                1000); // Remove the animation after 1 second
        });
    </script>
</body>

</html>
