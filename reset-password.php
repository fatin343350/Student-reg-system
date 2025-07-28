<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "student_system");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email exists
    $sql = "SELECT * FROM students WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        // Generate new random password
        $newPassword = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update DB
        $update = "UPDATE students SET password='$hashedPassword' WHERE email='$email'";
        if ($conn->query($update) === TRUE) {
            echo "✅ Your password has been reset.<br>";
            echo "🔑 New Password: <strong>$newPassword</strong><br><br>";
            echo "<a href='login.html'>Click here to Login</a>";
        } else {
            echo "❌ Failed to reset password.";
        }
    } else {
        echo "❌ No account found with that email.";
    }

    $conn->close();
}
?>
