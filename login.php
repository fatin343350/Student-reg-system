<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = new mysqli("localhost", "root", "", "student_system");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch student by email
    $sql = "SELECT * FROM students WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // ✅ Check approval status
            if ($user['status'] === 'approved') {
                $_SESSION['student_id'] = $user['id'];
                $_SESSION['student_name'] = $user['first_name'];
                header("Location: dashboard.php");
                exit();
            } elseif ($user['status'] === 'pending') {
                echo "<p style='color: orange; text-align: center;'>⏳ Your registration is pending admin approval.</p>";
            } elseif ($user['status'] === 'rejected') {
                echo "<p style='color: red; text-align: center;'>🚫 Your registration was rejected. Contact admin for help.</p>";
            }
        } else {
            echo "<p style='color: red; text-align: center;'>❌ Incorrect password.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>❌ No account found with that email.</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p style='color: red; text-align: center;'>🚫 Invalid request.</p>";
}
?>
