<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ Connect to the correct database
    $conn = new mysqli("localhost", "root", "", "student_system");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM lecturers WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $lecturer = $result->fetch_assoc();

        if (password_verify($password, $lecturer['password'])) {
            $_SESSION['lecturer_id'] = $lecturer['id'];
            $_SESSION['lecturer_name'] = $lecturer['name'];
            header("Location: lecturer-dashboard.php");
            exit();
        } else {
            echo "❌ Incorrect password.";
        }
    } else {
        echo "❌ Lecturer not found.";
    }

    $conn->close();
}
?>
