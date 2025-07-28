<?php
session_start();
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: lecturer-login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$lecturer_id = $_SESSION['lecturer_id'];

// Fetch units assigned to this lecturer
$units = $conn->query("SELECT unit_code FROM lecturer_units WHERE lecturer_id = $lecturer_id");

// Handle selected unit
$students = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unit_code'])) {
    $selected_unit = $_POST['unit_code'];
    $stmt = $conn->prepare("
        SELECT s.id, s.first_name, s.last_name 
        FROM student_units su 
        JOIN students s ON su.student_id = s.id 
        WHERE su.unit_code = ?
    ");
    $stmt->bind_param("s", $selected_unit);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grade Students</title>
    <link rel="stylesheet" href="lecturer-style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">L</div>
            <h2>Lecturer Portal</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="lecturer-dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="upload-notes.php"><i class="fas fa-file-upload"></i> <span>Upload Notes</span></a></li>
            <li><a href="grade-students.php"><i class="fas fa-graduation-cap"></i> <span>Grade Students</span></a></li>
            <li><a href="lecturer-timetable.php"><i class="fas fa-calendar-alt"></i> <span>Timetable</span></a></li>
            <li><a href="mark-attendance.php"><i class="fas fa-clipboard-check"></i> <span>Mark Attendance</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <div class="main">
        <h1>📝 Grade Students</h1>

        <form method="POST">
            <label>Select Unit:</label>
            <select name="unit_code" required>
                <option value="">-- Choose Unit --</option>
                <?php while ($row = $units->fetch_assoc()): ?>
                    <option value="<?php echo $row['unit_code']; ?>">
                        <?php echo $row['unit_code']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Load Students</button>
        </form>

        <?php if (!empty($students) && $students->num_rows > 0): ?>
            <form method="POST" action="save-grades.php">
                <input type="hidden" name="unit_code" value="<?php echo $selected_unit; ?>">
                <table border="1" cellpadding="10">
                    <tr>
                        <th>Student Name</th>
                        <th>Grade</th>
                    </tr>
                    <?php while ($s = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $s['first_name'] . " " . $s['last_name']; ?></td>
                            <td>
                                <input type="hidden" name="student_ids[]" value="<?php echo $s['id']; ?>">
                                <input type="text" name="grades[]"> <!-- Removed 'required' -->
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <br>
                <button type="submit">Save Grades</button>
            </form>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <p>No students found for this unit.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>