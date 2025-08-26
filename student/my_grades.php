<?php
// FILE: student/my_grades.php (UPDATED)

// Start the session
session_start();

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and the reusable navigation bar
require_once '../includes/db_connect.php';
require_once '../includes/student_nav.php';

$student_id = $_SESSION['user_id'];

// Fetch all classes and grades for the current student
$grades_query = "
    SELECT c.class_name, u.first_name AS teacher_first, u.last_name AS teacher_last, g.grade_value
    FROM student_classes sc
    JOIN classes c ON sc.class_id = c.id
    LEFT JOIN users u ON c.teacher_id = u.id
    LEFT JOIN grades g ON sc.student_id = g.student_id AND sc.class_id = g.class_id
    WHERE sc.student_id = ?
    ORDER BY c.class_name
";
$grades_stmt = $conn->prepare($grades_query);
$grades_stmt->bind_param("i", $student_id);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();
$grades_stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">


    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold text-center mb-8">My Grades</h1>
        
        <div class="bg-white p-8 rounded-2xl shadow-md overflow-x-auto">
            <h2 class="text-2xl font-bold mb-4">Your Academic Report</h2>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($grades_result->num_rows > 0): ?>
                        <?php while ($grade = $grades_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['class_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($grade['teacher_first'] . ' ' . $grade['teacher_last']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-lg">
                                    <?php echo htmlspecialchars($grade['grade_value'] ?? 'N/A'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">You are not enrolled in any classes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
