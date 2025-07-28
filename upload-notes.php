<?php
session_start();
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: lecturer-login.html");
    exit();
}

$lecturer_id = $_SESSION['lecturer_id'];
$message = "";

// Connect to DB
$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $unit_code = $_POST['unit_code'];
    $file = $_FILES['note_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $targetPath = "uploads/" . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO notes (lecturer_id, unit_code, title, file_name, upload_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("isss", $lecturer_id, $unit_code, $title, $filename);
            $stmt->execute();

            $message = "✅ Notes uploaded successfully!";
        } else {
            $message = "❌ Failed to move uploaded file.";
        }
    } else {
        $message = "❌ File upload error.";
    }
}

// Fetch lecturer's assigned units
$unit_stmt = $conn->prepare("SELECT u.unit_code, u.unit_name FROM lecturer_units lu JOIN units u ON lu.unit_code = u.unit_code WHERE lu.lecturer_id = ?");
$unit_stmt->bind_param("i", $lecturer_id);
$unit_stmt->execute();
$unit_result = $unit_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Notes</title>
    <link rel="stylesheet" href="lecturer-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            background: #f2f2f2;
            padding: 20px;
            border-radius: 10px;
        }
        input, select, button {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
        }
        .message {
            text-align: center;
            font-weight: bold;
            color: green;
        }
    </style>
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
            <div class="form-container">
                <h2>Upload Notes</h2>
                <form action="upload-notes.php" method="POST" enctype="multipart/form-data">
                    <label>Note Title</label>
                    <input type="text" name="title" required>

                    <label>Select Unit</label>
                    <select name="unit_code" required>
                        <option value="">-- Select Unit --</option>
                        <?php while ($row = $unit_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['unit_code']; ?>">
                                <?php echo $row['unit_code'] . " - " . $row['unit_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label>Choose File (PDF, DOCX, etc.)</label>
                    <input type="file" name="note_file" required>

                    <button type="submit">Upload</button>

                    <?php if ($message): ?>
                        <div class="message"><?php echo $message; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
