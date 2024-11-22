<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "web_server";
$password = "";
$dbname = "websiteDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    $userId = $_SESSION['user_id'];
    $sql = "SELECT TaskID, Task, Status, Created FROM Todo WHERE UserID = ? ORDER BY Created DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($tasks);
    exit();
}

// Handle POST requests (adding/updating tasks)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'add':
            if (isset($_POST['task'])) {
                $task = trim($_POST['task']);
                $userId = $_SESSION['user_id'];
                
                $sql = "INSERT INTO Todo (UserID, Task) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $userId, $task);
                
                if ($stmt->execute()) {
                    sendJsonResponse(true, 'Task added successfully');
                } else {
                    sendJsonResponse(false, 'Error adding task');
                }
            }
            break;
            
        case 'complete':
            if (isset($_POST['task_id'])) {
                $taskId = $_POST['task_id'];
                $userId = $_SESSION['user_id'];
                
                $sql = "UPDATE Todo SET Status = 'Completed' WHERE TaskID = ? AND UserID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $taskId, $userId);
                
                if ($stmt->execute()) {
                    sendJsonResponse(true, 'Task completed successfully');
                } else {
                    sendJsonResponse(false, 'Error completing task');
                }
            }
            break;
            
        case 'delete':
            if (isset($_POST['task_id'])) {
                $taskId = $_POST['task_id'];
                $userId = $_SESSION['user_id'];
                
                $sql = "DELETE FROM Todo WHERE TaskID = ? AND UserID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $taskId, $userId);
                
                if ($stmt->execute()) {
                    sendJsonResponse(true, 'Task deleted successfully');
                } else {
                    sendJsonResponse(false, 'Error deleting task');
                }
            }
            break;
    }
}

$conn->close();
?>
