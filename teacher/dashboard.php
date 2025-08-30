<?php
// FILE: teacher/dashboard.php
// Main teacher dashboard page

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/teacher_nav.php';

$teacher_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Fetch the classes taught by this teacher
$classes_query = "
    SELECT id, class_name
    FROM classes
    WHERE teacher_id = ?
    ORDER BY class_name ASC
";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->bind_param("i", $teacher_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();
$classes_stmt->close();
$conn->close();
?>

<div class="container mx-auto p-6 mt-8">
    <h1 class="text-4xl font-bold text-center mb-2">Hello, <?php echo htmlspecialchars($first_name); ?>!</h1>
    <h2 class="text-xl text-center text-gray-600 mb-8">Welcome to your Teacher Dashboard.</h2>
    
    <div class="bg-white p-8 rounded-2xl shadow-md">
        <h3 class="text-2xl font-bold mb-4">My Classes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($classes_result->num_rows > 0): ?>
                <?php while ($class = $classes_result->fetch_assoc()): ?>
                    <div class="bg-slate-200 p-6 rounded-xl shadow-sm border border-gray-300">
                        <h4 class="text-xl font-semibold mb-2 text-blue-600"><?php echo htmlspecialchars($class['class_name']); ?></h4><br>
                        <a href="classroom.php?class_id=<?php echo htmlspecialchars($class['id']); ?>" 
                           class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full transition duration-300">
                            Go to Classroom
                        </a>
                        <a href="manage_grades.php?class_id=<?php echo htmlspecialchars($class['id']); ?>" 
                           class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full transition duration-300">
                            Manage Grades
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center col-span-full">You are not teaching any classes yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

