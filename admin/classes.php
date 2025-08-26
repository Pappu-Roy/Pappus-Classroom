<?php
// FILE: admin/classes.php (UPDATED)

// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include the database connection and the reusable navigation bar
require_once '../includes/db_connect.php';
require_once '../includes/admin_nav.php';

$message = isset($_GET['message']) ? $_GET['message'] : '';
$message_type = isset($_GET['type']) ? $_GET['type'] : '';

// Handle form submission for adding a new class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_class'])) {
    $class_name = $_POST['class_name'];
    $teacher_id = $_POST['teacher_id'] !== '' ? intval($_POST['teacher_id']) : NULL;

    // Check if class name already exists
    $stmt = $conn->prepare("SELECT id FROM classes WHERE class_name = ?");
    $stmt->bind_param("s", $class_name);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $message = "Error: A class with this name already exists.";
        $message_type = "error";
    } else {
        // Insert new class into the database
        $stmt = $conn->prepare("INSERT INTO classes (class_name, teacher_id) VALUES (?, ?)");
        $stmt->bind_param("si", $class_name, $teacher_id);
        if ($stmt->execute()) {
            $message = "Class added successfully!";
            $message_type = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "error";
        }
    }
    $stmt->close();
}

// Fetch all teachers to populate the dropdown
$teachers_result = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY last_name");

// Fetch all classes to display in the table, including the teacher's name
$classes_query = "
    SELECT c.id, c.class_name, u.first_name, u.last_name
    FROM classes c
    LEFT JOIN users u ON c.teacher_id = u.id
    ORDER BY c.class_name
";
$classes_result = $conn->query($classes_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="container mx-auto p-6 mt-8">
        <h1 class="text-4xl font-bold text-center mb-8">Manage Classes</h1>

        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded-lg text-center <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Form to Add a New Class -->
        <div class="bg-white p-8 rounded-2xl shadow-md mb-8">
            <h2 class="text-2xl font-bold mb-4">Add New Class</h2>
            <form action="classes.php" method="POST">
                <div class="mb-4">
                    <label for="class_name" class="block text-gray-700 font-bold mb-2">Class Name</label>
                    <input type="text" id="class_name" name="class_name" required
                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-6">
                    <label for="teacher_id" class="block text-gray-700 font-bold mb-2">Assign Teacher</label>
                    <select id="teacher_id" name="teacher_id"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:border-blue-500">
                        <option value="">-- No Teacher Assigned --</option>
                        <?php while ($teacher = $teachers_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($teacher['id']); ?>">
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="add_class"
                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Add Class
                    </button>
                </div>
            </form>
        </div>

        <!-- Table to Display All Classes -->
        <div class="bg-white p-8 rounded-2xl shadow-md overflow-x-auto">
            <h2 class="text-2xl font-bold mb-4">Current Classes</h2>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($classes_result->num_rows > 0): ?>
                        <?php while ($class = $classes_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($class['id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($class['class_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        echo htmlspecialchars($class['first_name'] ? $class['first_name'] . ' ' . $class['last_name'] : 'N/A');
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="edit_class.php?id=<?php echo htmlspecialchars($class['id']); ?>" class="text-blue-600 hover:text-blue-800 font-semibold transition duration-300">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No classes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
