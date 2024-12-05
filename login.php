<?php
session_start();

// Database connection
$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic sanitization for username
    $login_user = trim($_POST['username']);
    $login_user = preg_replace("/[^A-Za-z0-9_]/", "", $login_user); // Remove non-alphanumeric characters
    $login_user = htmlspecialchars($login_user); // Escape any remaining special characters

    // Sanitize password (trim whitespace)
    $login_pass = trim($_POST['pass']);

    // Check if the username exists
    $sql = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $login_user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // User found, check password
            $user = $result->fetch_assoc();
            if (password_verify($login_pass, $user['PassHash'])) {
                // Password matches, start session
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                echo "Login successful! Welcome " . $user['Username'];
                // Redirect to dashboard or home page
                header("Location: ./dashboard.html");  // Modify with actual redirect page
                exit();
            } else {
                echo "Incorrect password!";
            }
        } else {
            echo "No user found with that username!";
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
