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

$timetables = $conn->query("SELECT * FROM timetable_files ORDER BY upload_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Timetable</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>S DASH</h2>
        <ul>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="courses.php">📚 My Courses</a></li>
            <li><a href="#">💰 Payments</a></li>
            <li><a href="#">📊 Grades</a></li>
            <li><a href="timetable.php">🗓️ Timetable</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>📅 Uploaded Timetables</h1>
        <div class="section">
            <ul class="notes">
                <?php if ($timetables->num_rows > 0): ?>
                    <?php while ($row = $timetables->fetch_assoc()): ?>
                        <li>
                            <a href="uploads/timetables/<?php echo urlencode($row['file_name']); ?>" target="_blank">
                                <?php echo htmlspecialchars($row['file_name']); ?>
                            </a>
                            <small style="color: gray;">
                                (Uploaded on <?php echo date("d M Y", strtotime($row['upload_date'])); ?>)
                            </small>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No timetable uploaded yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
