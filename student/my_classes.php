<?php
// FILE: student/my_classes.php
// Student's page to view class invitations and accepted classes

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accept_class'])) {
        $class_id = intval($_POST['class_id']);
        $update_stmt = $conn->prepare("UPDATE student_classes SET status = 'accepted' WHERE student_id = ? AND class_id = ?");
        $update_stmt->bind_param("ii", $student_id, $class_id);
        if ($update_stmt->execute()) {
            $message = "You have accepted the class!";
            $message_type = "success";
        } else {
            $message = "Error accepting class.";
            $message_type = "error";
        }
        $update_stmt->close();
    } elseif (isset($_POST['ignore_class'])) {
        $class_id = intval($_POST['class_id']);
        $delete_stmt = $conn->prepare("DELETE FROM student_classes WHERE student_id = ? AND class_id = ?");
        $delete_stmt->bind_param("ii", $student_id, $class_id);
        if ($delete_stmt->execute()) {
            $message = "You have ignored the class invitation.";
            $message_type = "success";
        } else {
            $message = "Error ignoring class.";
            $message_type = "error";
        }
        $delete_stmt->close();
    }
}

// Fetch pending invitations
$pending_query = "
    SELECT sc.class_id, c.class_name, u.first_name AS teacher_first, u.last_name AS teacher_last
    FROM student_classes sc
    JOIN classes c ON sc.class_id = c.id
    JOIN users u ON c.teacher_id = u.id
    WHERE sc.student_id = ? AND sc.status = 'pending'
";
$pending_stmt = $conn->prepare($pending_query);
$pending_stmt->bind_param("i", $student_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_stmt->close();

// Fetch accepted classes
$accepted_query = "
    SELECT sc.class_id, c.class_name, u.first_name AS teacher_first, u.last_name AS teacher_last
    FROM student_classes sc
    JOIN classes c ON sc.class_id = c.id
    JOIN users u ON c.teacher_id = u.id
    WHERE sc.student_id = ? AND sc.status = 'accepted'
";
$accepted_stmt = $conn->prepare($accepted_query);
$accepted_stmt->bind_param("i", $student_id);
$accepted_stmt->execute();
$accepted_result = $accepted_stmt->get_result();
$accepted_stmt->close();
$conn->close();

?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-4">My Classes</h1>
    <h2 class="text-xl text-center text-gray-600 mb-8">Manage Your Course Invitations and Enrolled Classes</h2>

    <?php if (!empty($message)): ?>
        <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Pending Invitations -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
            <h3 class="text-2xl font-bold mb-4">Pending Invitations</h3>
            <?php if ($pending_result->num_rows > 0): ?>
                <?php while ($class = $pending_result->fetch_assoc()): ?>
                    <div class="border-b pb-4 mb-4 last:mb-0 last:pb-0">
                        <h4 class="text-lg font-semibold text-blue-600"><?php echo htmlspecialchars($class['class_name']); ?></h4>
                        <p class="text-sm text-gray-500 mb-2">Teacher: <?php echo htmlspecialchars($class['teacher_first'] . ' ' . $class['teacher_last']); ?></p>
                        <form action="my_classes.php" method="POST" class="flex items-center space-x-2">
                            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['class_id']); ?>">
                            <button type="submit" name="accept_class" class="bg-green-500 hover:bg-green-600 text-white text-sm py-1 px-3 rounded-full transition duration-300">Accept</button>
                            <button type="submit" name="ignore_class" class="bg-red-500 hover:bg-red-600 text-white text-sm py-1 px-3 rounded-full transition duration-300">Ignore</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center">No pending class invitations.</p>
            <?php endif; ?>
        </div>

        <!-- Accepted Classes -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
            <h3 class="text-2xl font-bold mb-4">My Accepted Classes</h3>
            <?php if ($accepted_result->num_rows > 0): ?>
                <?php while ($class = $accepted_result->fetch_assoc()): ?>
                    <div class="border-b pb-4 mb-4 last:mb-0 last:pb-0 flex flex-col items-start space-y-2">
                        <div class="flex-grow">
                            <h4 class="text-lg font-semibold text-blue-600"><?php echo htmlspecialchars($class['class_name']); ?></h4>
                            <p class="text-sm text-gray-500">Teacher: <?php echo htmlspecialchars($class['teacher_first'] . ' ' . $class['teacher_last']); ?></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="classroom.php?class_id=<?php echo htmlspecialchars($class['class_id']); ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-sm py-1 px-3 rounded-full transition duration-300">Go to Classroom</a>
                            <a href="my_grades.php?class_id=<?php echo htmlspecialchars($class['class_id']); ?>" class="bg-purple-500 hover:bg-purple-600 text-white text-sm py-1 px-3 rounded-full transition duration-300">View My Grades</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center">You have not accepted any classes yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>