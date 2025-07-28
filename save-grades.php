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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_ids'], $_POST['grades'], $_POST['unit_code'])) {
    $student_ids = $_POST['student_ids'];
    $grades = $_POST['grades'];
    $unit_code = $_POST['unit_code'];

    $stmt = $conn->prepare("INSERT INTO grades (student_id, unit_code, grade, graded_by) VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE grade = VALUES(grade), graded_on = CURRENT_TIMESTAMP");

    for ($i = 0; $i < count($student_ids); $i++) {
        $student_id = $student_ids[$i];
        $grade = $grades[$i];
        $stmt->bind_param("issi", $student_id, $unit_code, $grade, $lecturer_id);
        $stmt->execute();
    }

    echo "✅ Grades saved successfully. <a href='grade-students.php'>Go back</a>";
} else {
    echo "❌ Invalid data submitted.";
}
