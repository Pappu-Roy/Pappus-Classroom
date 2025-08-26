<?php
// FILE: admin/edit_user.php

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

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$message_type = '';

// Check if a user ID was provided and the user exists
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    die("User not found.");
}
$user = $user_result->fetch_assoc();
$stmt->close();

// Handle form submission for updating a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?");
    $update_stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $user_id);
    if ($update_stmt->execute()) {
        $message = "User updated successfully!";
        $message_type = "success";
        // Refresh user data after update
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['email'] = $email;
        $user['role'] = $role;
    } else {
        $message = "Error: " . $update_stmt->error;
        $message_type = "error";
    }
    $update_stmt->close();
}

// Handle form submission for deleting a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);
    if ($delete_stmt->execute()) {
        $message = "User deleted successfully!";
        $message_type = "success";
        header("Location: users.php?message=" . urlencode("User deleted successfully!") . "&type=success");
        exit();
    } else {
        $message = "Error: " . $delete_stmt->error;
        $message_type = "error";
    }
    $delete_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold text-center mb-8">Edit User</h1>
        
        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-2xl shadow-md">
            <h2 class="text-2xl font-bold mb-4">Editing: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            
            <form action="edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required
                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required
                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label for="role" class="block text-gray-700 font-bold mb-2">Role</label>
                        <select id="role" name="role" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                            <option value="student" <?php if ($user['role'] == 'student') echo 'selected'; ?>>Student</option>
                            <option value="teacher" <?php if ($user['role'] == 'teacher') echo 'selected'; ?>>Teacher</option>
                            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <button type="submit" name="update_user"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Update User
                    </button>
                    <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?');"
                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Delete User
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
