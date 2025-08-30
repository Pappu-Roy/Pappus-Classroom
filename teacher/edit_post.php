<?php
// FILE: teacher/edit_post.php

// Start the session
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Include database connection and reusable components
require_once '../includes/db_connect.php';
require_once '../includes/teacher_nav.php';

$teacher_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$message = '';
$message_type = '';

// Fetch the post details and verify it belongs to the teacher
$post_query = "
    SELECT cp.post_content, cp.class_id
    FROM classroom_posts cp
    WHERE cp.id = ? AND cp.user_id = ?
";
$post_stmt = $conn->prepare($post_query);
$post_stmt->bind_param("ii", $post_id, $teacher_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();

if ($post_result->num_rows === 0) {
    die("Error: Invalid post ID or you are not authorized to edit this post.");
}
$post = $post_result->fetch_assoc();
$post_stmt->close();

// Handle form submission for updating post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_post'])) {
    $post_content = $_POST['post_content'];

    $update_stmt = $conn->prepare("UPDATE classroom_posts SET post_content = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sii", $post_content, $post_id, $teacher_id);

    if ($update_stmt->execute()) {
        $message = "Post updated successfully!";
        $message_type = "success";
        // Refresh data after update
        $post['post_content'] = $post_content;
    } else {
        $message = "Error: " . $update_stmt->error;
        $message_type = "error";
    }
    $update_stmt->close();
}

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_post'])) {
    $delete_stmt = $conn->prepare("DELETE FROM classroom_posts WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $post_id, $teacher_id);
    if ($delete_stmt->execute()) {
        header("Location: classroom.php?class_id=" . htmlspecialchars($post['class_id']) . "&message=Post deleted successfully!");
        exit();
    } else {
        $message = "Error deleting post: " . $delete_stmt->error;
        $message_type = "error";
    }
    $delete_stmt->close();
}
$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-8">Edit Post</h1>
    
    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-2xl shadow-md">
        <h2 class="text-2xl font-bold mb-4">Editing Your Post</h2>
        <form action="edit_post.php?id=<?php echo htmlspecialchars($post_id); ?>" method="POST">
            <div class="mb-6">
                <label for="post_content" class="block text-gray-700 font-bold mb-2">Post Content</label>
                <textarea id="post_content" name="post_content" rows="6" required
                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500"><?php echo htmlspecialchars($post['post_content']); ?></textarea>
            </div>
            <div class="flex justify-between items-center">
                <button type="submit" name="update_post"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                    Update Post
                </button>
                <button type="submit" name="delete_post" onclick="return confirm('Are you sure you want to delete this post?');"
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                    Delete Post
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
