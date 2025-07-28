<?php
session_start();
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: lecturer-login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $unit = $_POST['unit'];
    $lecturer_id = $_SESSION['lecturer_id'];

    $upload_dir = "uploads/";
    $filename = basename($_FILES["note_file"]["name"]);
    $target_file = $upload_dir . $filename;

    if (move_uploaded_file($_FILES["note_file"]["tmp_name"], $target_file)) {
        echo "✅ Note uploaded successfully.<br><a href='upload-notes.php'>Back</a>";
        // Here you can also insert record into DB if needed
    } else {
        echo "❌ Error uploading file.";
    }
}
?>
