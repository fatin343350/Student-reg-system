<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.html");
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['timetable_file']) && $_FILES['timetable_file']['error'] == 0) {
        $filename = basename($_FILES['timetable_file']['name']);
        $targetPath = "uploads/timetables/" . $filename;

        if (move_uploaded_file($_FILES['timetable_file']['tmp_name'], $targetPath)) {
            $conn = new mysqli("localhost", "root", "", "student_system");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("INSERT INTO timetable_files (file_name, upload_date) VALUES (?, NOW())");
            $stmt->bind_param("s", $filename);
            $stmt->execute();

            $message = "✅ Timetable uploaded successfully.";
        } else {
            $message = "❌ Failed to upload timetable.";
        }
    } else {
        $message = "❌ No file uploaded.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Timetable</title>
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
            <li><a href="pending-students.php">📥 Approve Students</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h2>📤 Upload Timetable File</h2>

        <?php if ($message): ?>
            <p style="color: green; font-weight: bold;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Select Timetable File (PDF, XLSX, etc.):</label>
            <input type="file" name="timetable_file" required>
            <br><br>
            <button type="submit">Upload Timetable</button>
        </form>
    </div>
</div>
</body>
</html>
