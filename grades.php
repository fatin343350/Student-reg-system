<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['student_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Grades</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>S DASH</h2>
        <ul>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="courses.php">📚 My Courses</a></li>
            <li><a href="payments.php">💰 Payments</a></li>
            <li><a href="grades.php">📊 Grades</a></li>
            <li><a href="timetable.php">🗓️ Timetable</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>📊 My Grades</h1>

        <?php
        $sql = "SELECT g.unit_code, g.grade, g.graded_on, l.name AS lecturer_name 
                FROM grades g 
                JOIN lecturers l ON g.graded_by = l.id 
                WHERE g.student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <tr>
                    <th>Unit Code</th>
                    <th>Grade</th>
                    <th>Lecturer</th>
                    <th>Graded On</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['unit_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['grade']); ?></td>
                        <td><?php echo htmlspecialchars($row['lecturer_name']); ?></td>
                        <td><?php echo date("d M Y", strtotime($row['graded_on'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No grades recorded yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>