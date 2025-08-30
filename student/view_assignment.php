<?php
// FILE: student/view_assignment.php
// Student's page to view assignment details and submit work

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/student_nav.php';

$student_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$message_type = '';

if ($assignment_id === 0) {
    die("Error: Assignment ID not provided.");
}

// Handle assignment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_assignment'])) {
    $text_code = $_POST['text_code'];
    $file_path = null;

    if (isset($_FILES['file_submission']) && $_FILES['file_submission']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['file_submission']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $full_file_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['file_submission']['tmp_name'], $full_file_path)) {
            $file_path = 'uploads/' . $new_filename;
        }
    }

    // Check if a submission already exists for this student and assignment
    $check_submission_stmt = $conn->prepare("SELECT id FROM submissions WHERE student_id = ? AND assignment_id = ?");
    $check_submission_stmt->bind_param("ii", $student_id, $assignment_id);
    $check_submission_stmt->execute();
    $check_submission_result = $check_submission_stmt->get_result();

    if ($check_submission_result->num_rows > 0) {
        // Update existing submission
        $update_submission_stmt = $conn->prepare("UPDATE submissions SET file_path = ?, text_code = ? WHERE student_id = ? AND assignment_id = ?");
        $update_submission_stmt->bind_param("siii", $file_path, $text_code, $student_id, $assignment_id);
        $update_submission_stmt->execute();
        $message = "Your submission has been updated successfully!";
        $message_type = "success";
        $update_submission_stmt->close();
    } else {
        // Insert new submission
        $insert_submission_stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, text_code) VALUES (?, ?, ?, ?)");
        $insert_submission_stmt->bind_param("iiss", $assignment_id, $student_id, $file_path, $text_code);
        $insert_submission_stmt->execute();
        $message = "Your assignment has been submitted successfully!";
        $message_type = "success";
        $insert_submission_stmt->close();
    }

    $check_submission_stmt->close();
}

// Fetch assignment details
$assignment_stmt = $conn->prepare("SELECT a.title, a.description, a.deadline, a.file_path, c.class_name FROM assignments a JOIN classes c ON a.class_id = c.id WHERE a.id = ?");
$assignment_stmt->bind_param("i", $assignment_id);
$assignment_stmt->execute();
$assignment_result = $assignment_stmt->get_result();
$assignment = $assignment_result->fetch_assoc();
$assignment_stmt->close();

if (!$assignment) {
    die("Error: Assignment not found.");
}

$class_name = $assignment['class_name'];
$deadline = new DateTime($assignment['deadline']);
$current_time = new DateTime();
$is_late = $current_time > $deadline;

// Check for existing submission by the student
$submission_stmt = $conn->prepare("SELECT file_path, text_code FROM submissions WHERE student_id = ? AND assignment_id = ?");
$submission_stmt->bind_param("ii", $student_id, $assignment_id);
$submission_stmt->execute();
$submission_result = $submission_stmt->get_result();
$existing_submission = $submission_result->fetch_assoc();
$submission_stmt->close();

$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-4"><?php echo htmlspecialchars($assignment['title']); ?></h1>
    <p class="text-xl text-center text-gray-600 mb-8">Class: <?php echo htmlspecialchars($class_name); ?></p>

    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Assignment Details -->
        <div class="bg-white p-8 rounded-2xl shadow-md">
            <h2 class="text-2xl font-bold mb-4">Assignment Details</h2>
            <div class="space-y-4 text-gray-700">
                <div>
                    <span class="font-semibold">Description:</span>
                    <p class="mt-1 bg-gray-100 p-3 rounded-md"><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                </div>
                <div>
                    <span class="font-semibold">Deadline:</span>
                    <p class="mt-1 bg-gray-100 p-3 rounded-md">
                        <?php echo htmlspecialchars($deadline->format('F j, Y, g:i a')); ?>
                        <?php if ($is_late): ?>
                            <span class="ml-2 text-red-600 font-bold"><br>⏰ Deadline Passed (Late Submission Counted)</span>
                        <?php endif; ?>
                    </p>

                </div>
                <?php if (!empty($assignment['file_path'])): ?>
                    <div>
                        <span class="font-semibold">Attached File:</span>
                        <p class="mt-1 bg-gray-100 p-3 rounded-md">
                            <a href="../<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank" class="text-green-600 hover:underline">Download Attached File</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Submission Form -->
        <div class="bg-white p-8 rounded-2xl shadow-md">
            <h2 class="text-2xl font-bold mb-4">Submit Your Work</h2>
            <form action="view_assignment.php?id=<?php echo htmlspecialchars($assignment_id); ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="text-code" class="block text-gray-700 font-bold mb-2">Text/Code Submission (Optional)</label>
                    <textarea name="text_code" id="text-code" rows="8" class="w-full border rounded-lg p-3 focus:outline-none focus:border-blue-500" placeholder="Paste your code or write your answer here..."><?php echo htmlspecialchars($existing_submission['text_code'] ?? ''); ?></textarea>
                </div>
                <div class="mb-6">
                    <label for="file_submission" class="block text-gray-700 font-bold mb-2">File Submission (Optional)</label>
                    <input type="file" name="file_submission" id="file_submission" class="w-full text-gray-700">
                    <?php if (!empty($existing_submission['file_path'])): ?>
                        <p class="text-sm mt-2 text-gray-500">
                            Current file submitted: <a href="../<?php echo htmlspecialchars($existing_submission['file_path']); ?>" target="_blank" class="text-green-600 hover:underline">Download</a>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="submit_assignment" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        <?php echo $existing_submission ? 'Update Submission' : 'Submit Assignment'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
