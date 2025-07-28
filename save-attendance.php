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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_ids'], $_POST['unit_code'], $_POST['status'], $_POST['date'])) {
    $student_ids = $_POST['student_ids'];
    $unit_code = $_POST['unit_code'];
    $status = $_POST['status'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO attendance (student_id, unit_code, status, marked_on, marked_by) 
        VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE status = VALUES(status)");

    $saved = 0;
    foreach ($student_ids as $student_id) {
        $student_status = $status[$student_id];
        $stmt->bind_param("isssi", $student_id, $unit_code, $student_status, $date, $lecturer_id);
        $stmt->execute();
        $saved++;
    }

    echo "✅ Attendance marked for $saved students. <a href='mark-attendance.php'>Go back</a>";
} else {
    echo "❌ Invalid data.";
}
?>
