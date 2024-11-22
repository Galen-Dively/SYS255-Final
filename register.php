<?php
session_start();

// Database connection
$servername = "localhost";
$username = "web_server";
$password = "jZ5/=nFwAuMRm_y(9%dBU";
$dbname = "websiteDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic sanitization
    $new_user = trim($_POST['username']);
    $password = trim($_POST['pass']);

    // Insert into database
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO Users (Username, PassHash) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $new_user, $password_hash);
        if ($stmt->execute()) {
            echo "User registered successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
