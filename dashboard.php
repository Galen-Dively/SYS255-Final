<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection using PDO with error handling
try {
    $dsn = "mysql:host=localhost;dbname=websiteDB;charset=utf8mb4";
    $username = "web_server";
    $password = "jZ5/=nFwAuMRm_y(9%dBU";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit();
}

// Function to return JSON response
function sendJsonResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Handle GET request for tasks
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'get_tasks') {
    try {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT TaskID, Task, Status, Created FROM Todo WHERE UserID = ? ORDER BY Created DESC");
        $stmt->execute([$userId]);
        $tasks = $stmt->fetchAll();
        
        sendJsonResponse(true, 'Tasks retrieved successfully', $tasks);
    } catch (PDOException $e) {
        error_log("Error retrieving tasks: " . $e->getMessage());
        sendJsonResponse(false, 'Error retrieving tasks');
    }
}

// Handle POST requests (adding/updating tasks)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $userId = $_SESSION['user_id'];
    
    try {
        switch($action) {
            case 'add':
                if (!isset($_POST['task'])) {
                    sendJsonResponse(false, 'Task content is required');
                }
                
                $task = trim($_POST['task']);
                
                // Validate task input
                if (strlen($task) < 3 || strlen($task) > 255) {
                    sendJsonResponse(false, 'Task must be between 3 and 255 characters');
                }
                
                $stmt = $pdo->prepare("INSERT INTO Todo (UserID, Task) VALUES (?, ?)");
                $stmt->execute([$userId, $task]);
                
                sendJsonResponse(true, 'Task added successfully');
                break;
                
            case 'complete':
                if (!isset($_POST['task_id'])) {
                    sendJsonResponse(false, 'Task ID is required');
                }
                
                $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
                if ($taskId === false) {
                    sendJsonResponse(false, 'Invalid task ID');
                }
                
                $stmt = $pdo->prepare("UPDATE Todo SET Status = 'Completed' WHERE TaskID = ? AND UserID = ?");
                $stmt->execute([$taskId, $userId]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Task not found or already completed');
                }
                
                sendJsonResponse(true, 'Task completed successfully');
                break;
                
            case 'delete':
                if (!isset($_POST['task_id'])) {
                    sendJsonResponse(false, 'Task ID is required');
                }
                
                $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
                if ($taskId === false) {
                    sendJsonResponse(false, 'Invalid task ID');
                }
                
                $stmt = $pdo->prepare("DELETE FROM Todo WHERE TaskID = ? AND UserID = ?");
                $stmt->execute([$taskId, $userId]);
                
                if ($stmt->rowCount() === 0) {
                    sendJsonResponse(false, 'Task not found');
                }
                
                sendJsonResponse(true, 'Task deleted successfully');
                break;

            default:
                sendJsonResponse(false, 'Invalid action');
                break;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(false, 'An error occurred while processing your request');
    }
}
