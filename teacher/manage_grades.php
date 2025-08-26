<?php
// FILE: teacher/manage_grades.php

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
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

$message = '';
$message_type = '';

// Check if a class ID was provided and it belongs to the current teacher
$check_class_stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ? AND teacher_id = ?");
$check_class_stmt->bind_param("ii", $class_id, $teacher_id);
$check_class_stmt->execute();
$class_result = $check_class_stmt->get_result();

if ($class_result->num_rows === 0) {
    die("Invalid class ID or you are not authorized to manage this class.");
}
$class_name = $class_result->fetch_assoc()['class_name'];
$check_class_stmt->close();

// Handle form submission for adding/updating grades
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_grades'])) {
    foreach ($_POST['grades'] as $student_id => $grade_value) {
        // Prevent empty grades from being inserted
        if (!empty($grade_value)) {
            // Check if a grade for this student and class already exists
            $check_grade_stmt = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND class_id = ?");
            $check_grade_stmt->bind_param("ii", $student_id, $class_id);
            $check_grade_stmt->execute();
            $check_grade_stmt->store_result();
            
            if ($check_grade_stmt->num_rows > 0) {
                // Update existing grade
                $update_stmt = $conn->prepare("UPDATE grades SET grade_value = ? WHERE student_id = ? AND class_id = ?");
                $update_stmt->bind_param("sii", $grade_value, $student_id, $class_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Insert new grade
                $insert_stmt = $conn->prepare("INSERT INTO grades (student_id, class_id, grade_value) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("iis", $student_id, $class_id, $grade_value);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
            $check_grade_stmt->close();
        }
    }
    $message = "Grades saved successfully!";
    $message_type = "success";
}

// Fetch all students and their current grades for this class
$students_query = "
    SELECT u.id, u.first_name, u.last_name, g.grade_value
    FROM users u
    LEFT JOIN grades g ON u.id = g.student_id AND g.class_id = ?
    WHERE u.role = 'student'
    ORDER BY u.last_name
";
$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $class_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grades - <?php echo htmlspecialchars($class_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold text-center mb-4">Manage Grades for:</h1>
        <h2 class="text-3xl text-center mb-8 text-blue-600"><?php echo htmlspecialchars($class_name); ?></h2>
        
        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-2xl shadow-md overflow-x-auto">
            <form action="manage_grades.php?class_id=<?php echo htmlspecialchars($class_id); ?>" method="POST">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Grade</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($students_result->num_rows > 0): ?>
                            <?php while ($student = $students_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" name="grades[<?php echo htmlspecialchars($student['id']); ?>]"
                                               value="<?php echo htmlspecialchars($student['grade_value'] ?? ''); ?>"
                                               class="px-2 py-1 border rounded-md w-24 text-center">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No students assigned to this class yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="mt-6 flex justify-end">
                    <button type="submit" name="save_grades"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Save Grades
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
