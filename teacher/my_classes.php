<?php
// FILE: teacher/my_classes.php (UPDATED)

// Start the session
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and the reusable navigation bar
require_once '../includes/db_connect.php';
require_once '../includes/teacher_nav.php';

$teacher_id = $_SESSION['user_id'];

// Fetch all classes assigned to the current teacher
$classes_query = "
    SELECT id, class_name
    FROM classes
    WHERE teacher_id = ?
    ORDER BY class_name
";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->bind_param("i", $teacher_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();

?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-8">My Classes</h1>
    
    <div class="bg-white p-8 rounded-2xl shadow-md overflow-x-auto">
        <h2 class="text-2xl font-bold mb-4">Classes I Teach</h2>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($classes_result->num_rows > 0): ?>
                    <?php while ($class = $classes_result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($class['class_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <a href="manage_grades.php?class_id=<?php echo htmlspecialchars($class['id']); ?>"
                                   class="text-blue-600 hover:text-blue-800 font-semibold transition duration-300">Manage Grades</a>
                                <span class="text-gray-400">|</span>
                                <a href="assign_students.php?class_id=<?php echo htmlspecialchars($class['id']); ?>"
                                   class="text-purple-600 hover:text-purple-800 font-semibold transition duration-300">Assign Students</a>
                                <span class="text-gray-400">|</span>
                                <a href="classroom.php?class_id=<?php echo htmlspecialchars($class['id']); ?>"
                                   class="text-green-600 hover:text-green-800 font-semibold transition duration-300">Classroom</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">You are not assigned to any classes.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

