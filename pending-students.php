<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students with 'Pending' status
$sql = "SELECT * FROM students WHERE status = 'Pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Students</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin-dashboard.php">🏠 Dashboard</a></li>
            <li><a href="assign-unit.php">📘 Assign Units</a></li>
            <li><a href="upload-timetable.php">🗓️ Upload Timetable</a></li>
            <li><a href="admin-register-lecturer.php">➕ Register Lecturer</a></li>
            <li><a href="pending-students.php">📋 Approve Students</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>⏳ Pending Student Approvals</h1>
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['course']; ?></td>
                        <td><a href="approve-student.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Approve this student?')">✅ Approve</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No pending students at the moment.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
