<?php
// FILE: dashboard.php

session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect based on user role
switch ($_SESSION['user_role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'teacher':
        header("Location: teacher/dashboard.php");
        break;
    case 'student':
        header("Location: student/dashboard.php");
        break;
    default:
        // Handle unexpected role or log out
        header("Location: logout.php");
        break;
}
exit();

?>
