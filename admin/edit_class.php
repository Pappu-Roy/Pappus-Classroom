<?php
// FILE: admin/edit_class.php

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

$class_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$message_type = '';

// Fetch the class data
$class_stmt = $conn->prepare("SELECT id, class_name, teacher_id FROM classes WHERE id = ?");
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();

if ($class_result->num_rows === 0) {
    die("Class not found.");
}
$class = $class_result->fetch_assoc();
$class_stmt->close();

// Handle form submission for updating a class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_class'])) {
    $class_name = $_POST['class_name'];
    $teacher_id = $_POST['teacher_id'] !== '' ? intval($_POST['teacher_id']) : NULL;
    
    // Update class details
    $update_stmt = $conn->prepare("UPDATE classes SET class_name = ?, teacher_id = ? WHERE id = ?");
    $update_stmt->bind_param("sii", $class_name, $teacher_id, $class_id);
    if ($update_stmt->execute()) {
        $message = "Class updated successfully!";
        $message_type = "success";
        $class['class_name'] = $class_name;
        $class['teacher_id'] = $teacher_id;
    } else {
        $message = "Error: " . $update_stmt->error;
        $message_type = "error";
    }
    $update_stmt->close();
}

// Handle form submission for deleting a class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_class'])) {
    $delete_stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $delete_stmt->bind_param("i", $class_id);
    if ($delete_stmt->execute()) {
        $message = "Class deleted successfully!";
        $message_type = "success";
        header("Location: classes.php?message=" . urlencode("Class deleted successfully!") . "&type=success");
        exit();
    } else {
        $message = "Error: " . $delete_stmt->error;
        $message_type = "error";
    }
    $delete_stmt->close();
}

// Handle form submission for adding students to the class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_students'])) {
    $student_ids = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];

    // First, delete all existing student enrollments for this class
    $delete_enrollments_stmt = $conn->prepare("DELETE FROM student_classes WHERE class_id = ?");
    $delete_enrollments_stmt->bind_param("i", $class_id);
    $delete_enrollments_stmt->execute();
    $delete_enrollments_stmt->close();

    // Then, insert new enrollments
    if (!empty($student_ids)) {
        $insert_enrollments_stmt = $conn->prepare("INSERT INTO student_classes (student_id, class_id) VALUES (?, ?)");
        foreach ($student_ids as $student_id) {
            $insert_enrollments_stmt->bind_param("ii", $student_id, $class_id);
            $insert_enrollments_stmt->execute();
        }
        $insert_enrollments_stmt->close();
    }
    $message = "Students updated successfully!";
    $message_type = "success";
}

// Fetch all teachers to populate the dropdown
$teachers_result = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY last_name");

// Fetch all students and their enrollment status for this class
$all_students_query = "
    SELECT u.id, u.first_name, u.last_name, 
           CASE WHEN sc.class_id IS NOT NULL THEN 1 ELSE 0 END AS is_enrolled
    FROM users u
    LEFT JOIN student_classes sc ON u.id = sc.student_id AND sc.class_id = ?
    WHERE u.role = 'student'
    ORDER BY u.last_name
";
$all_students_stmt = $conn->prepare($all_students_query);
$all_students_stmt->bind_param("i", $class_id);
$all_students_stmt->execute();
$all_students_result = $all_students_stmt->get_result();
$all_students_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">


    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold text-center mb-8">Edit Class</h1>
        
        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-2xl shadow-md mb-8">
            <h2 class="text-2xl font-bold mb-4">Class Details: <?php echo htmlspecialchars($class['class_name']); ?></h2>
            <form action="edit_class.php?id=<?php echo htmlspecialchars($class_id); ?>" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="class_name" class="block text-gray-700 font-bold mb-2">Class Name</label>
                        <input type="text" id="class_name" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required
                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label for="teacher_id" class="block text-gray-700 font-bold mb-2">Assign Teacher</label>
                        <select id="teacher_id" name="teacher_id"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                            <option value="">-- No Teacher Assigned --</option>
                            <?php while ($teacher = $teachers_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($teacher['id']); ?>" <?php if ($class['teacher_id'] == $teacher['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <button type="submit" name="update_class"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Update Class
                    </button>
                    <button type="submit" name="delete_class" onclick="return confirm('Are you sure you want to delete this class?');"
                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Delete Class
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-md">
            <h2 class="text-2xl font-bold mb-4">Manage Students in Class</h2>
            <form action="edit_class.php?id=<?php echo htmlspecialchars($class_id); ?>" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <?php if ($all_students_result->num_rows > 0): ?>
                        <?php while ($student = $all_students_result->fetch_assoc()): ?>
                            <div class="flex items-center">
                                <input type="checkbox" id="student_<?php echo htmlspecialchars($student['id']); ?>"
                                       name="student_ids[]" value="<?php echo htmlspecialchars($student['id']); ?>"
                                       class="rounded-md mr-2"
                                       <?php if ($student['is_enrolled']) echo 'checked'; ?>>
                                <label for="student_<?php echo htmlspecialchars($student['id']); ?>" class="text-gray-700">
                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500">No students found to enroll.</p>
                    <?php endif; ?>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="add_students"
                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Update Students
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
