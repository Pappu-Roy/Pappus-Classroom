<?php
// FILE: admin/dashboard.php

// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and the reusable navigation bar
require_once '../includes/db_connect.php';
require_once '../includes/admin_nav.php';

// Prepare and execute a query to get the count of each user role
$query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$result = $conn->query($query);
$counts = [];
while ($row = $result->fetch_assoc()) {
    $counts[$row['role']] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">


    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold mb-8 text-center">Admin Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card for Teachers -->
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold mb-2">Teachers</h2>
                <p class="text-4xl font-bold text-blue-600"><?php echo htmlspecialchars($counts['teacher'] ?? 0); ?></p>
            </div>
            
            <!-- Card for Students -->
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold mb-2">Students</h2>
                <p class="text-4xl font-bold text-green-600"><?php echo htmlspecialchars($counts['student'] ?? 0); ?></p>
            </div>
            
            <!-- Card for Admins -->
            <div class="bg-white p-6 rounded-2xl shadow-md text-center">
                <h2 class="text-xl font-semibold mb-2">Admins</h2>
                <p class="text-4xl font-bold text-purple-600"><?php echo htmlspecialchars($counts['admin'] ?? 0); ?></p>
            </div>
        </div>

        <div class="mt-12 text-center">
            <h2 class="text-3xl font-bold mb-4">Quick Actions</h2>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="users.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-full shadow-md transition duration-300">Manage Users</a>
                <a href="classes.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-full shadow-md transition duration-300">Manage Classes</a>
            </div>
        </div>
    </div>
</body>
</html>
