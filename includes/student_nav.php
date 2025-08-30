<?php
// FILE: includes/student_nav.php
// Reusable navigation bar for the student panel.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-800 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="dashboard.php" class="text-xl font-bold">Student Panel</a>
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="hover:text-gray-300">Dashboard</a>
                <a href="my_grades.php" class="hover:text-gray-300">My Grades</a>
                <a href="my_classes.php" class="hover:text-gray-300">My Classes</a>
                <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full transition duration-300">Logout</a>
            </div>
        </div>
    </nav>
