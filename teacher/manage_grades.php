<?php
// FILE: teacher/manage_grades.php
// Teacher's page to manage student grades within a specific class

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/teacher_nav.php';

$teacher_id = $_SESSION['user_id'];
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$message = '';
$message_type = '';

// Check if the teacher is authorized to manage this class
$check_class_stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ? AND teacher_id = ?");
if (!$check_class_stmt) {
    die("Database query error: " . $conn->error);
}
$check_class_stmt->bind_param("ii", $class_id, $teacher_id);
$check_class_stmt->execute();
$class_result = $check_class_stmt->get_result();

if ($class_result->num_rows === 0) {
    die("Error: Invalid class ID or you are not authorized to manage this class.");
}
$class_name = $class_result->fetch_assoc()['class_name'];
$check_class_stmt->close();

// Handle grade submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_grade'])) {
    $student_id = intval($_POST['student_id']);
    $assignment_id = intval($_POST['assignment_id']);
    $grade_value = $_POST['grade_value'];

    // Check if a grade already exists for this student and assignment
    $check_grade_stmt = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND assignment_id = ?");
    if (!$check_grade_stmt) {
        $message = "Database query error: " . $conn->error;
        $message_type = "error";
    } else {
        $check_grade_stmt->bind_param("ii", $student_id, $assignment_id);
        $check_grade_stmt->execute();
        $check_grade_result = $check_grade_stmt->get_result();

        if ($check_grade_result->num_rows > 0) {
            // Grade exists, so update it
            $update_grade_stmt = $conn->prepare("UPDATE grades SET grade_value = ? WHERE student_id = ? AND assignment_id = ?");
            if (!$update_grade_stmt) {
                $message = "Database query error: " . $conn->error;
                $message_type = "error";
            } else {
                $update_grade_stmt->bind_param("sii", $grade_value, $student_id, $assignment_id);
                if ($update_grade_stmt->execute()) {
                    $message = "Grade updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error updating grade: " . $conn->error;
                    $message_type = "error";
                }
                $update_grade_stmt->close();
            }
        } else {
            // No grade exists, so insert a new one
            $insert_grade_stmt = $conn->prepare("INSERT INTO grades (student_id, assignment_id, grade_value) VALUES (?, ?, ?)");
            if (!$insert_grade_stmt) {
                $message = "Database query error: " . $conn->error;
                $message_type = "error";
            } else {
                $insert_grade_stmt->bind_param("iis", $student_id, $assignment_id, $grade_value);
                if ($insert_grade_stmt->execute()) {
                    $message = "Grade assigned successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error assigning grade: " . $conn->error;
                    $message_type = "error";
                }
                $insert_grade_stmt->close();
            }
        }
        $check_grade_stmt->close();
    }
}

// Fetch all students in the class
$students_query = "
    SELECT u.id, u.first_name, u.last_name
    FROM users u
    JOIN student_classes sc ON u.id = sc.student_id
    WHERE sc.class_id = ? AND sc.status = 'accepted'
    ORDER BY u.last_name, u.first_name
";
$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $class_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();
$students_stmt->close();

$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-4">Manage Grades for:</h1>
    <h2 class="text-3xl text-center mb-8 text-blue-600"><?php echo htmlspecialchars($class_name); ?></h2>
    
    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-2xl shadow-md space-y-8">
        <?php if ($students_result->num_rows > 0): ?>
            <?php while ($student = $students_result->fetch_assoc()): ?>
                <div class="border-b pb-6 last:border-b-0">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>

                    <?php
                    // Re-establish connection to fetch assignments and submissions for each student
                    require '../includes/db_connect.php';

                    $student_assignments_query = "
                        SELECT a.id, a.title, a.deadline, s.text_code, s.file_path, s.submitted_at, g.grade_value
                        FROM assignments a
                        LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
                        LEFT JOIN grades g ON a.id = g.assignment_id AND g.student_id = ?
                        WHERE a.class_id = ?
                        ORDER BY a.deadline DESC
                    ";
                    $student_assignments_stmt = $conn->prepare($student_assignments_query);
                    if (!$student_assignments_stmt) {
                        echo "<p class='text-red-500'>Error: Could not prepare assignment query.</p>";
                    } else {
                        $student_assignments_stmt->bind_param("iii", $student['id'], $student['id'], $class_id);
                        $student_assignments_stmt->execute();
                        $student_assignments_result = $student_assignments_stmt->get_result();

                        if ($student_assignments_result->num_rows > 0) {
                            echo '<div class="space-y-6">';
                            while ($assignment = $student_assignments_result->fetch_assoc()) {
                                echo '<div class="bg-gray-100 p-6 rounded-lg shadow-inner">';
                                echo '<h4 class="text-lg font-semibold mb-2">' . htmlspecialchars($assignment['title']) . '</h4>';

                                // Text/Code Submission
                                if (!empty($assignment['text_code'])) {
                                    echo '<div class="my-2">';
                                    echo '<p class="font-bold text-sm">Text/Code Submission:</p>';
                                    echo '<pre class="whitespace-pre-wrap text-sm break-all mt-1 p-2 bg-white rounded-md">' . htmlspecialchars($assignment['text_code']) . '</pre>';
                                    echo '</div>';
                                }

                                // File Submission
                                if (!empty($assignment['file_path'])) {
                                    echo '<div class="my-2">';
                                    echo '<p class="font-bold text-sm">File Submission:</p>';
                                    echo '<a href="../' . htmlspecialchars($assignment['file_path']) . '" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs py-1 px-3 rounded-full transition duration-300">Download File</a>';
                                    echo '</div>';
                                }

                                // If no submission at all
                                if (empty($assignment['text_code']) && empty($assignment['file_path'])) {
                                    echo '<p class="text-gray-500 text-sm">No submission.</p>';
                                }

                                // Grade Form
                                echo '<form action="manage_grades.php?class_id=' . htmlspecialchars($class_id) . '" method="POST" class="mt-4 flex items-center space-x-2">';
                                echo '<input type="hidden" name="student_id" value="' . htmlspecialchars($student['id']) . '">';
                                echo '<input type="hidden" name="assignment_id" value="' . htmlspecialchars($assignment['id']) . '">';
                                echo '<input type="hidden" name="assign_grade" value="1">';
                                echo '<label for="grade-' . htmlspecialchars($assignment['id']) . '" class="text-sm font-bold">Grade:</label>';
                                echo '<input type="text" id="grade-' . htmlspecialchars($assignment['id']) . '" name="grade_value"';
                                echo 'value="' . htmlspecialchars($assignment['grade_value'] ?? '') . '"';
                                echo 'class="border border-gray-300 rounded-md p-1 w-24 text-center text-sm" placeholder="e.g., A+, 95">';
                                echo '<button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-xs py-1 px-3 rounded-full transition duration-300">Save</button>';
                                echo '</form>';

                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<p class="text-gray-500">No assignments for this student in this class.</p>';
                        }
                        $student_assignments_stmt->close();
                    }
                    $conn->close();
                    ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500 text-center">No students have been assigned to this class yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
