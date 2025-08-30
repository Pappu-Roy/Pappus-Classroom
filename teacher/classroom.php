<?php
// FILE: teacher/classroom.php

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

if ($class_id === 0) {
    die("Error: Class ID not provided.");
}

// Verify that the teacher is authorized to access this class
$check_auth_stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ? AND teacher_id = ?");
$check_auth_stmt->bind_param("ii", $class_id, $teacher_id);
$check_auth_stmt->execute();
$auth_result = $check_auth_stmt->get_result();

if ($auth_result->num_rows === 0) {
    die("Error: You are not authorized to view this class.");
}
$class_name = $auth_result->fetch_assoc()['class_name'];
$check_auth_stmt->close();

// Handle post creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_post'])) {
    $post_content = $_POST['post_content'];
    $file_path = null;

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
    $insert_post_stmt->bind_param("iiss", $class_id, $teacher_id, $post_content, $file_path);
    $insert_post_stmt->execute();
    $insert_post_stmt->close();

    $message = "Post created successfully!";
    $message_type = "success";
}

// Handle assignment creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_assignment'])) {
    $title = $_POST['assignment_title'];
    $description = $_POST['assignment_description'];
    $deadline = $_POST['assignment_deadline'];
    $file_path = null;

    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $file_path)) {
            $file_path = 'uploads/' . $new_filename;
        } else {
            $file_path = null;
        }
    }
    
    // Check if deadline is empty and set it to NULL for the database
    $deadline_db = !empty($deadline) ? $deadline : null;

    $insert_assignment_stmt = $conn->prepare("INSERT INTO assignments (class_id, teacher_id, title, description, deadline, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$insert_assignment_stmt) {
        die("Database query error: " . $conn->error);
    }
    $insert_assignment_stmt->bind_param("iissss", $class_id, $teacher_id, $title, $description, $deadline, $file_path);
    $insert_assignment_stmt->execute();
    $insert_assignment_stmt->close();

    $message = "Assignment created successfully!";
    $message_type = "success";
}

// Handle delete requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $type = $_POST['type'];
    $id = intval($_POST['id']);

    if ($type === 'post') {
        $delete_stmt = $conn->prepare("DELETE FROM classroom_posts WHERE id = ? AND user_id = ? AND class_id = ?");
        $delete_stmt->bind_param("iii", $id, $teacher_id, $class_id);
    } elseif ($type === 'assignment') {
        $delete_stmt = $conn->prepare("DELETE FROM assignments WHERE id = ? AND teacher_id = ? AND class_id = ?");
        $delete_stmt->bind_param("iii", $id, $teacher_id, $class_id);
    }

    if ($delete_stmt) {
        $delete_stmt->execute();
        $delete_stmt->close();
        $message = ucfirst($type) . " deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting " . $type . ".";
        $message_type = "error";
    }
}


// Fetch all posts for this class
$posts_query = "
    SELECT cp.id, cp.post_content, cp.file_path, cp.created_at, u.first_name, u.last_name
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

// Fetch all assignments for this class and order by creation date (id)
$assignments_query = "
    SELECT id, title, deadline, file_path
    FROM assignments
    WHERE class_id = ?
    ORDER BY id DESC
";
$assignments_stmt = $conn->prepare($assignments_query);
$assignments_stmt->bind_param("i", $class_id);
$assignments_stmt->execute();
$assignments_result = $assignments_stmt->get_result();
$assignments_stmt->close();

$conn->close();
?>

<div class="container mx-auto max-w-8xl px-4 md:px-8 lg:px-16 py-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-4"><?php echo htmlspecialchars($class_name); ?></h1>
    <h2 class="text-2xl text-center text-gray-600 mb-8">Classroom</h2>

    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold mb-4">Create a New Assignment</h3>
                <form action="classroom.php?class_id=<?php echo htmlspecialchars($class_id); ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="assignment_title" class="block text-gray-700 font-bold mb-2">Assignment Title</label>
                        <input type="text" name="assignment_title" id="assignment_title" required class="w-full border rounded-lg p-2 focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="assignment_description" class="block text-gray-700 font-bold mb-2">Description</label>
                        <textarea name="assignment_description" id="assignment_description" rows="4" class="w-full border rounded-lg p-2 focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="assignment_deadline" class="block text-gray-700 font-bold mb-2">Deadline</label>
                        <input type="datetime-local" name="assignment_deadline" id="assignment_deadline" required class="w-full border rounded-lg p-2 focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Attach File (Optional)</label>
                        <input type="file" name="assignment_file" class="w-full text-gray-700">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="new_assignment" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                            Create Assignment
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold mb-4">Create a New Post</h3>
                <form action="classroom.php?class_id=<?php echo htmlspecialchars($class_id); ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <textarea name="post_content" rows="4" class="w-full border rounded-lg p-3 focus:outline-none focus:border-blue-500" placeholder="What's on your mind?"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Attach File (Optional)</label>
                        <input type="file" name="post_file" class="w-full text-gray-700">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="new_post" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                            Post
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold mb-4">Classroom Feed</h3>
                <div class="space-y-4">
                    <?php if ($posts_result->num_rows > 0): ?>
                        <?php while ($post = $posts_result->fetch_assoc()): ?>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex items-start justify-between">
                                <div>
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
                                <div class="flex items-center space-x-3">
                                    <a href="edit_post.php?id=<?php echo htmlspecialchars($post['id']); ?>" 
                                    class="text-sm text-blue-500 hover:text-blue-700 font-medium">Edit</a>
                                    <form action="classroom.php?class_id=<?php echo htmlspecialchars($class_id); ?>" 
                                        method="POST" 
                                        onsubmit="return confirm('Are you sure you want to delete this post?');" 
                                        class="inline-block">
                                        <input type="hidden" name="type" value="post">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($post['id']); ?>">
                                        <button type="submit" name="delete" 
                                                class="text-sm text-red-500 hover:text-red-700 font-medium">Delete</button>
                                    </form>
                                </div>

                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center">No posts yet. Start the conversation!</p>
                    <?php endif; ?>
                </div>
            </div>

            
        </div>

        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold mb-4">Assignments</h3>
                <div class="space-y-4">
                    <?php if ($assignments_result->num_rows > 0): ?>
                        <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex items-center justify-between">
                                <div>
                                    <a href="view_assignment.php?id=<?php echo htmlspecialchars($assignment['id']); ?>" class="text-lg font-semibold text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($assignment['title']); ?>
                                    </a>
                                    <p class="text-sm text-gray-500">
                                        Deadline: <?php
                                        $deadline = $assignment['deadline'];
                                        if ($deadline && strtotime($deadline) > 0) {
                                            echo date('F j, Y, g:i a', strtotime($deadline));
                                        } else {
                                            echo "No deadline set";
                                        }
                                        ?>
                                    </p>
                                    <?php if (!empty($assignment['file_path'])): ?>
                                        <p class="text-sm mt-1">
                                            <a href="../<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank" class="text-green-600 hover:underline">Download Attached File</a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <a href="edit_assignment.php?id=<?php echo htmlspecialchars($assignment['id']); ?>" class="text-sm text-blue-500 hover:text-blue-700 font-medium">Edit</a>
                                    <form action="classroom.php?class_id=<?php echo htmlspecialchars($class_id); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                        <input type="hidden" name="type" value="assignment">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($assignment['id']); ?>">
                                        <button type="submit" name="delete" class="text-sm text-red-500 hover:text-red-700 font-medium">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center">No assignments created yet.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
