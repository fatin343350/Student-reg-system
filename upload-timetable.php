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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['timetable_file'])) {
    $file = $_FILES['timetable_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        // Create uploads/timetables folder if not exists
        $uploadDir = "uploads/timetables/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // ✅ Clean and rename file
        $originalName = basename($file['name']);
        $safeName = time() . "-" . preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $originalName);
        $targetPath = $uploadDir . $safeName;

        // ✅ Move file
        if (move_uploaded_file($file["tmp_name"], $targetPath)) {
            // ✅ Save into DB
            $stmt = $conn->prepare("INSERT INTO timetable_files (file_name, upload_date) VALUES (?, NOW())");
            $stmt->bind_param("s", $safeName);
            $stmt->execute();
            $message = "✅ Timetable uploaded successfully!";
        } else {
            $message = "❌ Failed to upload file.";
        }
    } else {
        $message = "❌ Error during file upload.";
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
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>📤 Upload Timetable</h1>

        <form action="upload-timetable.php" method="POST" enctype="multipart/form-data">
            <label>Select Timetable File</label><br><br>
            <input type="file" name="timetable_file" required>
            <br><br>
            <button type="submit">Upload</button>
        </form>

        <?php if (!empty($message)) : ?>
            <p style="margin-top: 20px; font-weight: bold;"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
