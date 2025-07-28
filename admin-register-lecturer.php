<!-- admin-register-lecturer.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Lecturer</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin-dashboard.php">🏠 Dashboard</a></li>
            <li><a href="assign-unit.php">📘 Assign Units</a></li>
            <li><a href="upload-timetable.php">🗓️ Upload Timetable</a></li>
            <li><a href="admin-register-lecturer.php">➕ Register Lecturer</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h2>Register New Lecturer</h2>
        <form action="process-register-lecturer.php" method="POST">
            <label>Full Name</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Register Lecturer</button>
        </form>
    </div>
</div>
</body>
</html>
