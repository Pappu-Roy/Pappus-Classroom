<?php
// FILE: teacher/view_assignment.php
// Teacher's page to view assignment details, student submissions, and assign grades

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/teacher_nav.php';

$teacher_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$message_type = '';

// Check if the teacher is authorized to view this assignment
$check_auth_stmt = $conn->prepare("SELECT a.id, c.id AS class_id FROM assignments a JOIN classes c ON a.class_id = c.id WHERE a.id = ? AND c.teacher_id = ?");
if (!$check_auth_stmt) {
    die("Database query error: " . $conn->error);
}
$check_auth_stmt->bind_param("ii", $assignment_id, $teacher_id);
$check_auth_stmt->execute();
$auth_result = $check_auth_stmt->get_result();

if ($auth_result->num_rows === 0) {
    die("Error: Invalid assignment ID or you are not authorized to view this assignment.");
}
$class_id = $auth_result->fetch_assoc()['class_id'];
$check_auth_stmt->close();

// Handle grade submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_grade'])) {
    $student_id = intval($_POST['student_id']);
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

// Fetch assignment details
$assignment_query = "
    SELECT a.id, a.title, a.description, a.deadline, c.class_name
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    WHERE a.id = ?
";
$assignment_stmt = $conn->prepare($assignment_query);
$assignment_stmt->bind_param("i", $assignment_id);
$assignment_stmt->execute();
$assignment_result = $assignment_stmt->get_result();
if ($assignment_result->num_rows === 0) {
    die("Error: Assignment not found.");
}
$assignment = $assignment_result->fetch_assoc();
$assignment_stmt->close();

// Fetch all student submissions and grades for this assignment
$submissions_query = "
    SELECT u.id AS student_id, u.first_name, u.last_name, s.text_code, s.file_path, s.submitted_at, g.grade_value
    FROM users u
    JOIN student_classes sc ON u.id = sc.student_id
    LEFT JOIN submissions s ON u.id = s.student_id AND s.assignment_id = ?
    LEFT JOIN grades g ON u.id = g.student_id AND g.assignment_id = ?
    WHERE sc.class_id = ? AND sc.status = 'accepted' AND u.role = 'student'
    ORDER BY u.last_name, u.first_name
";
$submissions_stmt = $conn->prepare($submissions_query);
$submissions_stmt->bind_param("iii", $assignment_id, $assignment_id, $class_id);
$submissions_stmt->execute();
$submissions_result = $submissions_stmt->get_result();
$submissions_stmt->close();
$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-4"><?php echo htmlspecialchars($assignment['title']); ?></h1>
    <h2 class="text-2xl text-center text-gray-600 mb-8">Class: <?php echo htmlspecialchars($assignment['class_name']); ?></h2>

    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-2xl shadow-md space-y-6">
        <div>
            <h3 class="text-xl font-bold mb-2">Description</h3>
            <p class="text-gray-700 whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
        </div>
        <div>
            <h3 class="text-xl font-bold mb-2">Deadline</h3>
            <p class="text-gray-700"><?php echo date('F j, Y, g:i a', strtotime($assignment['deadline'])); ?></p>
        </div>
        
        <h3 class="text-2xl font-bold mb-4">Student Submissions</h3>
        <div class="space-y-6">
            <?php if ($submissions_result->num_rows > 0): ?>
                <?php while ($submission = $submissions_result->fetch_assoc()): ?>
                    <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-200">
                        <h4 class="text-lg font-bold text-blue-600"><?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?></h4>
                        <p class="text-sm text-gray-500 mb-2">
                            <?php if (!empty($submission['text_code']) || !empty($submission['file_path'])): ?>
                                Submitted
                            <?php else: ?>
                                Not Submitted
                            <?php endif; ?>
                        </p>
                        
                        <?php if (!empty($submission['text_code'])): ?>
                            <div class="my-2 p-3 bg-gray-100 rounded-lg">
                                <p class="font-bold text-sm">Text/Code Submission:</p>
                                <pre class="whitespace-pre-wrap text-sm break-all mt-1"><?php echo htmlspecialchars($submission['text_code']); ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($submission['file_path'])): ?>
                            <div class="my-2">
                                <p class="font-bold text-sm">File Submission:</p>
                                <a href="../<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs py-1 px-3 rounded-full transition duration-300">
                                    Download File
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <form action="view_assignment.php?id=<?php echo htmlspecialchars($assignment_id); ?>" method="POST" class="mt-4 flex items-center space-x-2">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($submission['student_id']); ?>">
                            <input type="hidden" name="assign_grade" value="1">
                            <label for="grade-<?php echo htmlspecialchars($submission['student_id']); ?>" class="text-sm">Grade:</label>
                            <input type="text" id="grade-<?php echo htmlspecialchars($submission['student_id']); ?>" name="grade_value"
                                value="<?php echo htmlspecialchars($submission['grade_value'] ?? ''); ?>"
                                class="border border-gray-300 rounded-md p-1 w-24 text-center text-sm"
                                placeholder="e.g., A+, 95">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-xs py-1 px-3 rounded-full transition duration-300">Save Grade</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center">No students have submitted this assignment yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
