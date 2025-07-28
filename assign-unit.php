<?php
// assign-unit.php
session_start();

// Optional: check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin-login.html");
//     exit();
//}

// Connect to DB
$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all lecturers
$lecturers = $conn->query("SELECT id, name FROM lecturers");

// Fetch all units
$units = $conn->query("SELECT unit_code, unit_name FROM units");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Units to Lecturers</title>
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
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main">
        <h2>Assign Unit to Lecturer</h2>

        <form method="POST" action="process-assign-unit.php">
            <label>Select Lecturer:</label>
            <select name="lecturer_id" required>
                <option value="">--Select Lecturer--</option>
                <?php while ($lec = $lecturers->fetch_assoc()): ?>
                    <option value="<?php echo $lec['id']; ?>"><?php echo $lec['name']; ?></option>
                <?php endwhile; ?>
            </select>

            <label>Select Unit:</label>
            <select name="unit_code" required>
                <option value="">--Select Unit--</option>
                <?php while ($unit = $units->fetch_assoc()): ?>
                    <option value="<?php echo $unit['unit_code']; ?>"><?php echo $unit['unit_name']; ?></option>
                <?php endwhile; ?>
            </select>

            <br><br>
            <button type="submit">Assign Unit</button>
        </form>
    </div>
</div>
</body>
</html>
