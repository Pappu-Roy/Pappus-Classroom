<?php
// FILE: teacher/edit_assignment.php

// Start the session
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Include database connection and reusable components
require_once '../includes/db_connect.php';
require_once '../includes/teacher_nav.php';

$teacher_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$message = '';
$message_type = '';

// Fetch the assignment details and verify it belongs to the teacher
$assignment_query = "
    SELECT a.title, a.description, a.deadline, c.class_name, c.id AS class_id
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    WHERE a.id = ? AND a.teacher_id = ?
";
$assignment_stmt = $conn->prepare($assignment_query);
$assignment_stmt->bind_param("ii", $assignment_id, $teacher_id);
$assignment_stmt->execute();
$assignment_result = $assignment_stmt->get_result();

if ($assignment_result->num_rows === 0) {
    die("Error: Invalid assignment ID or you are not authorized to edit this assignment.");
}
$assignment = $assignment_result->fetch_assoc();
$assignment_stmt->close();

// Handle form submission for updating assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_assignment'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];

    $update_stmt = $conn->prepare("UPDATE assignments SET title = ?, description = ?, deadline = ? WHERE id = ? AND teacher_id = ?");
    $update_stmt->bind_param("sssii", $title, $description, $deadline, $assignment_id, $teacher_id);

    if ($update_stmt->execute()) {
        $message = "Assignment updated successfully!";
        $message_type = "success";
        // Refresh data after update
        $assignment['title'] = $title;
        $assignment['description'] = $description;
        $assignment['deadline'] = $deadline;
    } else {
        $message = "Error: " . $update_stmt->error;
        $message_type = "error";
    }
    $update_stmt->close();
}

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_assignment'])) {
    $delete_stmt = $conn->prepare("DELETE FROM assignments WHERE id = ? AND teacher_id = ?");
    $delete_stmt->bind_param("ii", $assignment_id, $teacher_id);
    if ($delete_stmt->execute()) {
        header("Location: classroom.php?class_id=" . htmlspecialchars($assignment['class_id']) . "&message=Assignment deleted successfully!");
        exit();
    } else {
        $message = "Error deleting assignment: " . $delete_stmt->error;
        $message_type = "error";
    }
    $delete_stmt->close();
}
$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-8">Edit Assignment</h1>
    
    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-2xl shadow-md">
        <h2 class="text-2xl font-bold mb-4">Editing: <?php echo htmlspecialchars($assignment['title']); ?></h2>
        <form action="edit_assignment.php?id=<?php echo htmlspecialchars($assignment_id); ?>" method="POST">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required
                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea id="description" name="description" rows="4" required
                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500"><?php echo htmlspecialchars($assignment['description']); ?></textarea>
            </div>
            <div class="mb-6">
                <label for="deadline" class="block text-gray-700 font-bold mb-2">Deadline</label>
                <input type="datetime-local" id="deadline" name="deadline" value="<?php echo date('Y-m-d\TH:i', strtotime($assignment['deadline'])); ?>" required
                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex justify-between items-center">
                <button type="submit" name="update_assignment"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                    Update Assignment
                </button>
                <button type="submit" name="delete_assignment" onclick="return confirm('Are you sure you want to delete this assignment?');"
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                    Delete Assignment
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
