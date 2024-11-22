<?php
session_start();

// Database connection
$servername = "localhost";
$username = "web_server";
$password = "";
$dbname = "websiteDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Directly retrieve and use form input without sanitization
    $new_user = $_POST['username'];
    $password = $_POST['pass'];

    // Directly store the plain-text password in the database
    $sql = "INSERT INTO Users (Username, PassHash) VALUES ('$new_user', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "User registered successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
