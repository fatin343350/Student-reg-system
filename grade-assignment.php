<?php
session_start();
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: lecturer-login.html");
    exit();
}

if (!isset($_GET['unit_code']) || !isset($_GET['title'])) {
    die("Missing assignment info.");
}

$unit_code = $_GET['unit_code'];
$title = $_GET['title'];

$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students registered for this unit
$sql = "SELECT s.id, s.first_name, s.last_name 
        FROM students s
        INNER JOIN student_units su ON s.id = su.student_id
        WHERE su.unit_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $unit_code);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grade Assignment</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>L DASH</h2>
        <ul>
            <li><a href="lecturer-dashboard.php">🏠 Dashboard</a></li>
            <li><a href="upload-notes.php">📂 Upload Notes</a></li>
            <li><a href="lecturer-notes.php">📄 My Notes</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h2>📝 Grade Students – <?php echo htmlspecialchars($unit_code); ?> / <?php echo htmlspecialchars($title); ?></h2>
        <form method="POST" action="save-grades.php">
            <input type="hidden" name="unit_code" value="<?php echo htmlspecialchars($unit_code); ?>">
            <input type="hidden" name="title" value="<?php echo htmlspecialchars($title); ?>">

            <table border="1" cellpadding="10">
                <tr>
                    <th>Student Name</th>
                    <th>Grade</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['first_name'] . " " . $row['last_name']; ?></td>
                        <td>
                            <input type="hidden" name="student_ids[]" value="<?php echo $row['id']; ?>">
                            <input type="number" name="grades[]" min="0" max="100" required>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <br>
            <button type="submit">✅ Submit Grades</button>
        </form>
    </div>
</div>
</body>
</html>
