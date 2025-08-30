<?php
// FILE: student/dashboard.php
// Main student dashboard page

session_start();

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/student_nav.php';

$student_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Fetch the classes the student is enrolled in with 'accepted' status
$classes_query = "
    SELECT c.id, c.class_name, u.first_name AS teacher_first, u.last_name AS teacher_last
    FROM classes c
    JOIN student_classes sc ON c.id = sc.class_id
    JOIN users u ON c.teacher_id = u.id
    WHERE sc.student_id = ? AND sc.status = 'accepted'
    ORDER BY c.class_name ASC
";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->bind_param("i", $student_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();

$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-2">Hello, <?php echo htmlspecialchars($first_name); ?>!</h1>
    <h2 class="text-xl text-center text-gray-600 mb-8">Welcome to your Student Dashboard.</h2>
    
    <div class="bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold mb-4">My Enrolled Classes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($classes_result->num_rows > 0): ?>
                <?php while ($class = $classes_result->fetch_assoc()): ?>
                    <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-200">
                        <h4 class="text-xl font-semibold mb-2 text-blue-600"><?php echo htmlspecialchars($class['class_name']); ?></h4>
                        <p class="text-gray-500 mb-4">Teacher: <?php echo htmlspecialchars($class['teacher_first'] . ' ' . $class['teacher_last']); ?></p>
                        <a href="classroom.php?class_id=<?php echo htmlspecialchars($class['id']); ?>" 
                           class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full transition duration-300">
                            Go to Classroom
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center col-span-full">You are not enrolled in any classes yet. Check your <a href="my_classes.php" class="text-blue-500 underline">invitations</a>!</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>