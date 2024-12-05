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

// Function to validate password strength
function validatePasswordStrength($password) {
    // Example: Password must have at least 8 characters, one upper case, one lower case, and one number
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic sanitization for username
    $new_user = trim($_POST['username']);
    $new_user = preg_replace("/[^A-Za-z0-9_]/", "", $new_user); // Remove non-alphanumeric characters
    $new_user = htmlspecialchars($new_user); // Escape any remaining special characters

    // Basic sanitization for password
    $password = trim($_POST['pass']);
    if (!validatePasswordStrength($password)) {
        echo "Password must be at least 8 characters long and contain both letters and numbers.";
        exit;
    }

    // Insert into database
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Check if the username already exists
    $sql_check = "SELECT COUNT(*) FROM Users WHERE Username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $new_user);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    if ($count > 0) {
        echo "Username is already taken.";
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();

    // Proceed to insert the new user
    $sql = "INSERT INTO Users (Username, PassHash) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $new_user, $password_hash);
        if ($stmt->execute()) {
	    header("Location: ./login.html");
            echo "User registered successfully!";
        } else {
            echo "Error: Could not execute query.";
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
