<?php
// FILE: student/classroom.php
// Student's page to view class posts and assignments

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/student_nav.php';

$student_id = $_SESSION['user_id'];
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

// Verify that the student is enrolled and has accepted the class
$check_enrollment_stmt = $conn->prepare("SELECT c.class_name FROM student_classes sc JOIN classes c ON sc.class_id = c.id WHERE sc.student_id = ? AND sc.class_id = ? AND sc.status = 'accepted'");
$check_enrollment_stmt->bind_param("ii", $student_id, $class_id);
$check_enrollment_stmt->execute();
$enrollment_result = $check_enrollment_stmt->get_result();

if ($enrollment_result->num_rows === 0) {
    die("Error: Invalid class ID or you are not authorized to view this class.");
}
$class_name = $enrollment_result->fetch_assoc()['class_name'];
$check_enrollment_stmt->close();

// Handle new post submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_post'])) {
    $post_content = $_POST['post_content'];
    $file_path = null;
    
    // Handle file upload
    if (isset($_FILES['post_file']) && $_FILES['post_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['post_file']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['post_file']['tmp_name'], $file_path)) {
            $file_path = 'uploads/' . $new_filename;
        } else {
            $file_path = null;
        }
    }

    $insert_post_stmt = $conn->prepare("INSERT INTO classroom_posts (class_id, user_id, post_content, file_path) VALUES (?, ?, ?, ?)");
    if (!$insert_post_stmt) {
        die("Database query error: " . $conn->error);
    }
    $insert_post_stmt->bind_param("iiss", $class_id, $student_id, $post_content, $file_path);
    $insert_post_stmt->execute();
    $insert_post_stmt->close();
}

// Fetch all posts for this class
$posts_query = "
    SELECT cp.post_content, cp.file_path, cp.created_at, u.first_name, u.last_name
    FROM classroom_posts cp
    JOIN users u ON cp.user_id = u.id
    WHERE cp.class_id = ?
    ORDER BY cp.created_at DESC
";
$posts_stmt = $conn->prepare($posts_query);
$posts_stmt->bind_param("i", $class_id);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();
$posts_stmt->close();

// Fetch all assignments for this class
$assignments_query = "
    SELECT id, title, description, deadline, file_path
    FROM assignments 
    WHERE class_id = ? 
    ORDER BY deadline DESC
";
$assignments_stmt = $conn->prepare($assignments_query);
$assignments_stmt->bind_param("i", $class_id);
$assignments_stmt->execute();
$assignments_result = $assignments_stmt->get_result();
$assignments_stmt->close();
$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-4"><?php echo htmlspecialchars($class_name); ?></h1>
    <h2 class="text-2xl text-center text-gray-600 mb-8">Classroom</h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content Area: Assignments & Posts -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Student Post Section -->
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold mb-4">Post to Classroom</h3>
                <form action="classroom.php?class_id=<?php echo htmlspecialchars($class_id); ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <textarea name="post_content" rows="4" class="w-full border rounded-lg p-3 focus:outline-none focus:border-blue-500" placeholder="What's on your mind?"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Attach File (Optional)</label>
                        <input type="file" name="post_file" class="w-full text-gray-700">
                    </div>
                    <button type="submit" name="new_post" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Post
                    </button>
                </form>
            </div>

            <!-- Classroom Feed Section -->
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold mb-4">Classroom Feed</h3>
                <div class="space-y-4">
                    <?php if ($posts_result->num_rows > 0): ?>
                        <?php while ($post = $posts_result->fetch_assoc()): ?>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                <div class="flex items-center mb-2">
                                    <span class="font-bold mr-2 text-blue-600"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($post['created_at']); ?></span>
                                </div>
                                <?php if (!empty($post['post_content'])): ?>
                                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($post['post_content'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($post['file_path'])): ?>
                                    <p class="text-sm mt-2">
                                        <a href="../<?php echo htmlspecialchars($post['file_path']); ?>" target="_blank" class="text-green-600 hover:underline">Download Attached File</a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center">No posts yet. Start the conversation!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Sidebar: Assignments List -->
        <div class="bg-white p-6 rounded-2xl shadow-md h-fit">
            <h3 class="text-xl font-bold mb-4">Assignments</h3>
            <div class="space-y-4">
                <?php if ($assignments_result->num_rows > 0): ?>
                    <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                        <div class="border-b pb-2 last:border-b-0">
                            <a href="view_assignment.php?id=<?php echo htmlspecialchars($assignment['id']); ?>" class="text-lg font-semibold text-blue-600 hover:underline"><?php echo htmlspecialchars($assignment['title']); ?></a>
                            <p class="text-sm text-gray-500">Deadline: <?php echo date('F j, Y, g:i a', strtotime($assignment['deadline'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center">No assignments created yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>