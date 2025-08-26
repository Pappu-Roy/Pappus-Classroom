<?php
// FILE: create_and_test.php
// This script will:
// 1. Check for the database connection.
// 2. Automatically create the admin user with a freshly generated password hash.
// 3. Display the login form.

// Start the session
session_start();

// Database credentials
$servername = "localhost";
$username = "root";
$password = "root"; // Default XAMPP password
$dbname = "student_management_system";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Generate a new, correct password hash for the password "password"
$fresh_password_hash = password_hash('password', PASSWORD_DEFAULT);
$admin_email = 'admin@example.com';

// Check if the admin user exists. If so, delete it and create a fresh one.
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Admin user exists, so delete it to prevent primary key errors
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $delete_stmt->bind_param("s", $admin_email);
    $delete_stmt->execute();
    $delete_stmt->close();
}
$stmt->close();

// Insert the new admin user with the freshly generated password hash
$insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Admin', 'User', ?, ?, 'admin')");
$insert_stmt->bind_param("ss", $admin_email, $fresh_password_hash);
if ($insert_stmt->execute()) {
    $message = "Admin user created successfully! Email: " . $admin_email . ", Password: **password**";
} else {
    $message = "Error creating admin user: " . $insert_stmt->error;
}
$insert_stmt->close();

$conn->close();

// Redirect to the regular login page after creation
header("Location: login.php?status=" . urlencode($message));
exit();
?>
