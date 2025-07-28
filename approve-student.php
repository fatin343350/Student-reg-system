<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.html");
    exit();
}

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $conn = new mysqli("localhost", "root", "", "student_system");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Approve student
    $stmt = $conn->prepare("UPDATE students SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        header("Location: pending-students.php?msg=Student approved successfully");
        exit();
    } else {
        echo "❌ Failed to approve student: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "❌ No student ID provided.";
}
?>
