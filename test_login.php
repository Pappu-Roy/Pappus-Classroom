<?php
// FILE: test_login.php
// This is a temporary file to test database connection and login functionality.

// Start the session
session_start();

// Database credentials - Make sure these are correct for your XAMPP setup
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "student_management_system";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Check if the admin user exists. If not, create it.
$email_to_check = 'admin@example.com';
$admin_password_hash = '$2y$10$tF.D7sX3bK8.jH7gQ3cQd.kO4hQ4q8.f4x5fC0X.b.L7c0zL4L.e.'; // Password is 'password'

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email_to_check);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Admin user does not exist, so insert it
    $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Admin', 'User', ?, ?, 'admin')");
    $insert_stmt->bind_param("ss", $email_to_check, $admin_password_hash);
    if ($insert_stmt->execute()) {
        $message = "Admin user created successfully! Please try to log in.";
    } else {
        $message = "Error creating admin user.";
    }
    $insert_stmt->close();
} else {
    // Admin user already exists
    $message = "Admin user already exists. You can try to log in.";
}
$stmt->close();


$error_message = '';

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $login_stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
    $login_stmt->bind_param("s", $email);
    $login_stmt->execute();
    $login_result = $login_stmt->get_result();

    if ($login_result->num_rows > 0) {
        $user = $login_result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect to the correct dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Invalid email or password.";
        }
    } else {
        $error_message = "Invalid email or password.";
    }

    $login_stmt->close();
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center mb-6">Login Test</h2>
        
        <?php if (!empty($message)): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <form action="test_login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email address</label>
                <input type="email" id="email" name="email" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500" value="admin@example.com">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
            </div>
            <div class="flex items-center justify-center">
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline transition duration-300">
                    Sign In
                </button>
            </div>
        </form>
    </div>
</body>
</html>
