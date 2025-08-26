<?php
// FILE: register.php

// Start the session to clear it
session_start();

// Include the database connection file
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

$message = '';
$message_type = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'student'; // New users register as students by default

    // Basic validation
    if ($password !== $confirm_password) {
        $message = "Error: Passwords do not match.";
        $message_type = "error";
    } else {
        // Prepare a statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Error: A user with this email already exists.";
            $message_type = "error";
        } else {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into the database
            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);
            if ($insert_stmt->execute()) {
                $message = "Account created successfully! You can now log in.";
                $message_type = "success";
            } else {
                $message = "Error: " . $insert_stmt->error;
                $message_type = "error";
            }
            $insert_stmt->close();
        }
    }
    $stmt->close();
}
$conn->close();
?>

<div class="container mx-auto p-8 flex-grow flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-lg">
        <h2 class="text-2xl font-bold text-center mb-6">Create a Student Account</h2>
        
        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name</label>
                    <input type="text" id="first_name" name="first_name" required
                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required
                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email address</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 font-bold mb-2">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline transition duration-300 w-full">
                    Register
                </button>
            </div>
            <div class="mt-4 text-center">
                <a href="login.php" class="text-blue-500 hover:underline">Already have an account? Log in.</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
