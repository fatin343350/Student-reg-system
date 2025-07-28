<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['timetable_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $destination = "uploads/timetables/" . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $conn = new mysqli("localhost", "root", "", "student_system");
            if ($conn->connect_error) die("DB Error");

            $stmt = $conn->prepare("INSERT INTO timetable_files (file_name) VALUES (?)");
            $stmt->bind_param("s", $filename);
            $stmt->execute();
            echo "✅ Timetable uploaded successfully!";
        } else {
            echo "❌ Failed to move file.";
        }
    } else {
        echo "❌ File upload error.";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="timetable_file" required>
    <button type="submit">Upload Timetable</button>
</form>
