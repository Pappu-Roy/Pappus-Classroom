<?php
// FILE: includes/db_connect.php

// Database credentials
// Remember to change these if your MySQL settings are different!
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "root";     // Default XAMPP password
$dbname = "student_management_system";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Stop script execution and display an error if connection fails
    die("Connection failed: " . $conn->connect_error);
}
?>
