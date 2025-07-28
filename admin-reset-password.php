<?php
session_start();
$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle reset form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['new_password'])) {
    $email = $_POST['email'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Check if email exists
    $check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update password
        $update = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
        $update->bind_param("ss", $new_password, $email);
        $update->execute();
        $message = "✅ Password reset successful.";
    } else {
        $message = "❌ Email not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Admin Password</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .reset-box {
            background: #fff;
            padding: 30px;
            margin: 100px auto;
            max-width: 400px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="reset-box">
    <h2>Reset Admin Password</h2>
    <?php if (!empty($message)) echo "<p style='color: green;'>$message</p>"; ?>
    <form method="POST" action="admin-reset-password.php">
        <input type="email" name="email" placeholder="Enter Admin Email" required>
        <input type="password" name="new_password" placeholder="Enter New Password" required>
        <button type="submit">Reset Password</button>
    </form>
    <p style="text-align:center;"><a href="admin-login.html">🔙 Back to Login</a></p>
</div>
</body>
</html>
