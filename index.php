<?php
// FILE: index.php

// Include the reusable header and footer
require_once 'includes/header.php';
?>

<div class="container mx-auto p-8 flex-grow">
    <header class="text-center py-16">
        <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-4 animate-fade-in">Welcome to Student Hub</h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto mb-8 animate-slide-up">
            Your all-in-one platform for seamless academic management.
        </p>
        <div class="space-x-4">
            <a href="login.php" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-blue-700 transition duration-300 transform hover:scale-105">
                Get Started
            </a>
            <a href="register.php" class="border border-gray-400 text-gray-700 font-bold py-3 px-8 rounded-full hover:bg-gray-200 transition duration-300 transform hover:scale-105">
                Create Account
            </a>
        </div>
    </header>

    <section class="grid grid-cols-1 md:grid-cols-3 gap-8 py-12 text-center">
        <!-- Feature Card 1 -->
        <div class="bg-white p-8 rounded-2xl shadow-lg transform transition-transform duration-300 hover:scale-105">
            <div class="text-5xl mb-4 text-blue-500">
                📚
            </div>
            <h3 class="text-xl font-semibold mb-2">Manage Classes</h3>
            <p class="text-gray-600">Teachers and admins can easily manage classes and student enrollment.</p>
        </div>
        
        <!-- Feature Card 2 -->
        <div class="bg-white p-8 rounded-2xl shadow-lg transform transition-transform duration-300 hover:scale-105">
            <div class="text-5xl mb-4 text-green-500">
                📈
            </div>
            <h3 class="text-xl font-semibold mb-2">Track Grades</h3>
            <p class="text-gray-600">Students can view their grades, while teachers can add and update them.</p>
        </div>
        
        <!-- Feature Card 3 -->
        <div class="bg-white p-8 rounded-2xl shadow-lg transform transition-transform duration-300 hover:scale-105">
            <div class="text-5xl mb-4 text-purple-500">
                👨‍🏫
            </div>
            <h3 class="text-xl font-semibold mb-2">Teacher & Student Portals</h3>
            <p class="text-gray-600">Dedicated dashboards for each role to streamline their daily tasks.</p>
        </div>
    </section>
</div>

<?php
// Include the reusable footer
require_once 'includes/footer.php';
?>
