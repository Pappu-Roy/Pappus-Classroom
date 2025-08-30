<?php
// FILE: teacher/assign_students.php (CORRECTED)

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

$check_class_stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ? AND teacher_id = ?");
$check_class_stmt->bind_param("ii", $class_id, $teacher_id);
$check_class_stmt->execute();
$class_result = $check_class_stmt->get_result();

if ($class_result->num_rows === 0) {
    die("Error: Invalid class ID or you are not authorized to manage this class.");
}
$class_name = $class_result->fetch_assoc()['class_name'];
$check_class_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_students'])) {
    $student_ids = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];

    // Delete existing enrollments that are not accepted
    $delete_enrollments_stmt = $conn->prepare("DELETE FROM student_classes WHERE class_id = ? AND status != 'accepted'");
    if ($delete_enrollments_stmt === false) {
        die("Error preparing delete statement: " . $conn->error);
    }
    $delete_enrollments_stmt->bind_param("i", $class_id);
    $delete_enrollments_stmt->execute();
    $delete_enrollments_stmt->close();

    // Insert new enrollments with 'pending' status
    if (!empty($student_ids)) {
        // Corrected statement: Checks for prepare failure
        $insert_enrollments_stmt = $conn->prepare("INSERT INTO student_classes (student_id, class_id, status) VALUES (?, ?, 'pending') ON DUPLICATE KEY UPDATE status='pending'");
        if ($insert_enrollments_stmt === false) {
            die("Error preparing insert statement: " . $conn->error);
        }
        foreach ($student_ids as $student_id) {
            $insert_enrollments_stmt->bind_param("ii", $student_id, $class_id);
            $insert_enrollments_stmt->execute();
        }
        $insert_enrollments_stmt->close();
    }
    $message = "Students updated successfully! They will see a pending invitation.";
    $message_type = "success";
}

$all_students_query = "
    SELECT u.id, u.first_name, u.last_name, sc.status
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
$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-4">Assign Students for:</h1>
    <h2 class="text-3xl text-center mb-8 text-blue-600"><?php echo htmlspecialchars($class_name); ?></h2>
    
    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-2xl shadow-md">
        <h2 class="text-2xl font-bold mb-4">Manage Students in This Class</h2>
        <form action="assign_students.php?class_id=<?php echo htmlspecialchars($class_id); ?>" method="POST">
            <div class="flex items-center mb-4">
                <input type="checkbox" id="select-all-students" class="rounded-md mr-2">
                <label for="select-all-students" class="text-gray-700 font-bold">Select All Students</label>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <?php if ($all_students_result->num_rows > 0): ?>
                    <?php while ($student = $all_students_result->fetch_assoc()): ?>
                        <div class="flex items-center">
                            <input type="checkbox" id="student_<?php echo htmlspecialchars($student['id']); ?>"
                                   name="student_ids[]" value="<?php echo htmlspecialchars($student['id']); ?>"
                                   class="student-checkbox rounded-md mr-2"
                                   <?php if ($student['status'] === 'accepted' || $student['status'] === 'pending') echo 'checked'; ?>>
                            <label for="student_<?php echo htmlspecialchars($student['id']); ?>" class="text-gray-700">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                <?php if ($student['status'] === 'accepted'): ?>
                                    <span class="text-xs text-green-500">(Accepted)</span>
                                <?php elseif ($student['status'] === 'pending'): ?>
                                    <span class="text-xs text-yellow-500">(Pending)</span>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500">No students found to enroll.</p>
                <?php endif; ?>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="update_students"
                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                    Update Students
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('select-all-students').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
</body>
</html>
