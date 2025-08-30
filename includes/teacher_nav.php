<?php
// FILE: includes/teacher_nav.php (UPDATED)

// This file contains the reusable HTML for the teacher navigation menu.
// It will be included in all teacher-specific pages.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Panel</title>
    <!-- Use Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

<nav class="bg-indigo-950 p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <div class="text-white text-2xl font-bold">Teacher Panel</div>
        <div class="space-x-4">
            <a href="dashboard.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md">Dashboard</a>
            <a href="my_classes.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md">My Classes</a>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-700 text-white px-3 py-2 rounded-md transition duration-300">Logout</a>
        </div>
    </div>
</nav>
