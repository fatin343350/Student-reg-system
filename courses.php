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

// Fetch student units
$sql = "SELECT u.unit_code, u.unit_name 
        FROM student_units su 
        JOIN units u ON su.unit_code = u.unit_code 
        WHERE su.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$unit_results = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Courses</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .course-boxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        .course-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 250px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .course-card h3 {
            margin: 0 0 10px;
        }
        .notes {
            font-size: 0.9em;
            color: #2980b9;
        }
        .notes a {
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>S DASH</h2>
            <ul>
                <li><a href="dashboard.php">🏠 Dashboard</a></li>
                <li><a href="courses.php">📚 My Courses</a></li>
                <li><a href="#">💰 Payments</a></li>
                <li><a href="grades.php">📊 Grades</a></li>
                <li><a href="timetable.php">🗓️ Timetable</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>
        <div class="main">
            <h1>My Registered Units</h1>
            <div class="course-boxes">
                <?php while ($unit = $unit_results->fetch_assoc()): ?>
                    <div class="course-card">
                        <h3><?php echo $unit['unit_code']; ?></h3>
                        <p><?php echo $unit['unit_name']; ?></p>
                        <div class="notes">
                            <strong>Notes:</strong>
                            <?php
                                $unit_code = $unit['unit_code'];
                                $notes_sql = "SELECT title, file_name FROM notes WHERE unit_code = ?";
                                $note_stmt = $conn->prepare($notes_sql);
                                $note_stmt->bind_param("s", $unit_code);
                                $note_stmt->execute();
                                $notes_result = $note_stmt->get_result();

                                if ($notes_result->num_rows > 0) {
                                    while ($note = $notes_result->fetch_assoc()) {
                                        echo "<a href='uploads/{$note['file_name']}' target='_blank'>📄 {$note['title']}</a>";
                                    }
                                } else {
                                    echo "<em>No notes uploaded yet</em>";
                                }
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>
