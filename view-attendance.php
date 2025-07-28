<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student-login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['student_id'];

$result = $conn->query("
    SELECT unit_code, status, marked_on 
    FROM attendance 
    WHERE student_id = $student_id 
    ORDER BY marked_on DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Attendance</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>STUDENT DASH</h2>
        <ul>
            <li><a href="/registration_system/dashboard.php">🏠 Dashboard</a></li>
            <li><a href="view-attendance.php">📅 My Attendance</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>📅 My Attendance</h1>
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <tr>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['unit_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['marked_on']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No attendance records found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
