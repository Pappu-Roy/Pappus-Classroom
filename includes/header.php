<?php
// FILE: includes/header.php

// This file contains the reusable header and navigation menu for all pages.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <!-- Use Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

<!-- Mobile-Responsive Navigation Bar -->
<nav class="bg-gray-800 p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <!-- Logo/Site Title -->
        <a href="index.php" class="text-white text-2xl font-bold">Student Hub</a>

        <!-- Mobile Menu Button -->
        <button id="mobile-menu-btn" class="text-gray-300 md:hidden focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>

        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex space-x-4">
            <a href="index.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md transition duration-300">Home</a>
            <a href="login.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md transition duration-300">Login</a>
            <a href="register.php" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md transition duration-300">Register</a>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="hidden md:hidden">
        <div class="flex flex-col space-y-2 mt-4 px-2">
            <a href="index.php" class="text-white hover:text-gray-300 px-3 py-2 rounded-md transition duration-300 bg-gray-700">Home</a>
            <a href="login.php" class="text-white hover:text-gray-300 px-3 py-2 rounded-md transition duration-300 bg-gray-700">Login</a>
            <a href="register.php" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md transition duration-300">Register</a>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        var mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
</script>
