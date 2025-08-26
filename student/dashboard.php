<?php
// FILE: student/dashboard.php

// Start the session
session_start();

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and the reusable navigation bar
require_once '../includes/db_connect.php';
require_once '../includes/student_nav.php';

// Get the user's ID from the session
$student_id = $_SESSION['user_id'];

// Get the student's name
$user_stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $student_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$first_name = $user['first_name'];
$user_stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">


    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold mb-8 text-center">Hello, <?php echo htmlspecialchars($first_name); ?>!</h1>
        
        <div class="bg-white p-8 rounded-2xl shadow-md text-center">
            <h2 class="text-2xl font-semibold mb-2">Welcome to your Student Dashboard.</h2>
            <p class="text-lg text-gray-600">Here you can view your grades and academic information.</p>
            <div class="mt-6">
                <a href="my_grades.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-full shadow-md transition duration-300">View My Grades</a>
            </div>
        </div>
    </div>
</body>
</html>
