<?php
// FILE: includes/db_connect.php
// This file connects to the database but does NOT close the connection.

$servername = "localhost";
$username = "root";
$password = "root"; // Replace with your MySQL password if you have one
$dbname = "student_management_system";

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8");

?>
