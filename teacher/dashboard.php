<?php
// FILE: teacher/dashboard.php

// Start the session
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and the reusable navigation bar
require_once '../includes/db_connect.php';
require_once '../includes/teacher_nav.php';

// Get the user's ID from the session
$teacher_id = $_SESSION['user_id'];

// Get the teacher's name
$user_stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $teacher_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$first_name = $user['first_name'];
$user_stmt->close();


// Query to count classes assigned to this teacher
$classes_query = "SELECT COUNT(*) AS class_count FROM classes WHERE teacher_id = ?";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->bind_param("i", $teacher_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();
$class_count = $classes_result->fetch_assoc()['class_count'];
$classes_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold mb-8 text-center">Hello, <?php echo htmlspecialchars($first_name); ?>!</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card for Classes Taught -->
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold mb-2">Classes Taught</h2>
                <p class="text-4xl font-bold text-blue-600"><?php echo htmlspecialchars($class_count); ?></p>
            </div>
            
            <!-- Card for Upcoming Assignments (Placeholder for now) -->
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold mb-2">Upcoming Tasks</h2>
                <p class="text-xl text-gray-500">No upcoming tasks. Good job!</p>
            </div>
        </div>

        <div class="mt-12 text-center">
            <h2 class="text-3xl font-bold mb-4">Quick Actions</h2>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="my_classes.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-full shadow-md transition duration-300">View My Classes</a>
            </div>
        </div>
    </div>
</body>
</html>
