<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "web_server", "", "websiteDB");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_user = trim($_POST['username']);
    $login_pass = trim($_POST['pass']);

    // Query user by username
    $stmt = $conn->prepare("SELECT UserID, Username, PassHash FROM Users WHERE Username = ?");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $stmt->bind_result($user_id, $username, $pass_hash);

    if ($stmt->fetch() && password_verify($login_pass, $pass_hash)) {
        // Successful login
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        header("Location: ./dashboard.html");
        exit();
    } else {
        echo "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>
