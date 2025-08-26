<?php
// FILE: includes/student_nav.php

// This file contains the reusable HTML for the student navigation menu.
// It will be included in all student-specific pages.
?>
<nav class="bg-gray-800 p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <div class="text-white text-2xl font-bold">Student Panel</div>
        <div class="space-x-4">
            <a href="dashboard.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md">Dashboard</a>
            <a href="my_grades.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md">My Grades</a>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-700 text-white px-3 py-2 rounded-md transition duration-300">Logout</a>
        </div>
    </div>
</nav>
