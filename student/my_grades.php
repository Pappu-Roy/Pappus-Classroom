<?php
// FILE: student/my_grades.php
// Student's page to view their grades

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/student_nav.php';

$student_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

$grades_query = "
    SELECT c.class_name, a.title AS assignment_title, g.grade_value
    FROM grades g
    JOIN assignments a ON g.assignment_id = a.id
    JOIN classes c ON a.class_id = c.id
    WHERE g.student_id = ?
    ORDER BY c.class_name, a.deadline DESC
";
$grades_stmt = $conn->prepare($grades_query);
if (!$grades_stmt) {
    die("Error preparing statement: " . $conn->error);
}
$grades_stmt->bind_param("i", $student_id);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();
$grades_stmt->close();

$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-8">My Grades</h1>
    
    <div class="bg-white p-8 rounded-2xl shadow-md overflow-x-auto">
        <?php if ($grades_result->num_rows > 0): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($grade = $grades_result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['class_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($grade['assignment_title']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <?php echo htmlspecialchars($grade['grade_value']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500">You have no grades to display yet.</p>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>
